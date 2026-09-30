<?php

/**
 * Bumps every constraint in composer.json to the newest minor release of its
 * current major version, as long as that release keeps supporting every PHP
 * version the currently declared release supports (within the project's own
 * "php" constraint).
 *
 * Usage: php update-dependencies.php <path/to/composer.json> <path/to/semver/vendor/autoload.php>
 *
 * The composer.json file is edited in place with targeted string replacements
 * so that its formatting is preserved. A Markdown summary of the applied
 * changes is written to STDOUT; nothing is written when no update is found.
 */

declare(strict_types=1);

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;

[, $composerJsonPath, $semverAutoloadPath] = $argv + [null, null, null];

if ($composerJsonPath === null || $semverAutoloadPath === null) {
    fwrite(STDERR, "Usage: php update-dependencies.php <composer.json> <semver autoload.php>\n");
    exit(1);
}

require $semverAutoloadPath;

$composerJsonContents = file_get_contents($composerJsonPath);
$composerJson = json_decode($composerJsonContents, true, 512, JSON_THROW_ON_ERROR);
$versionParser = new VersionParser();

$projectPhpConstraint = $versionParser->parseConstraints($composerJson['require']['php'] ?? '*');
$targetPhpVersions = array_values(array_filter(
    buildKnownPhpVersions(),
    static fn (string $phpVersion): bool => $projectPhpConstraint->matches($versionParser->parseConstraints($phpVersion))
));

$appliedUpdates = [];

foreach (['require', 'require-dev'] as $section) {
    foreach ($composerJson[$section] ?? [] as $packageName => $packageConstraint) {
        if (!str_contains($packageName, '/')) {
            continue; // Platform packages (php, ext-*, lib-*)
        }

        $packageReleases = fetchStableReleases($packageName, $versionParser);

        if ($packageReleases === []) {
            fwrite(STDERR, "Skipping {$packageName}: no stable releases found on Packagist.\n");
            continue;
        }

        $constraintBranches = preg_split('/\s*\|\|?\s*/', trim($packageConstraint));
        $updatedBranches = [];

        foreach ($constraintBranches as $constraintBranch) {
            $updatedBranches[] = bumpConstraintBranch(
                $constraintBranch,
                $packageReleases,
                $targetPhpVersions,
                $versionParser
            ) ?? $constraintBranch;
        }

        $updatedConstraint = implode(' || ', $updatedBranches);

        if ($updatedBranches === $constraintBranches) {
            continue;
        }

        $constraintPattern = '/("' . preg_quote($packageName, '/') . '"\s*:\s*)"' . preg_quote($packageConstraint, '/') . '"/';
        $composerJsonContents = preg_replace($constraintPattern, '${1}' . json_encode($updatedConstraint, JSON_UNESCAPED_SLASHES), $composerJsonContents, 1);
        $appliedUpdates[] = [$section, $packageName, $packageConstraint, $updatedConstraint];
    }
}

if ($appliedUpdates === []) {
    exit(0);
}

file_put_contents($composerJsonPath, $composerJsonContents);

echo "| Section | Package | From | To |\n";
echo "|---|---|---|---|\n";

foreach ($appliedUpdates as [$section, $packageName, $previousConstraint, $updatedConstraint]) {
    // Pipes must be escaped, even inside code spans, to not break the Markdown table
    $previousConstraint = str_replace('|', '\|', $previousConstraint);
    $updatedConstraint = str_replace('|', '\|', $updatedConstraint);

    echo "| `{$section}` | [`{$packageName}`](https://packagist.org/packages/{$packageName}) | `{$previousConstraint}` | `{$updatedConstraint}` |\n";
}

/**
 * Builds the list of PHP versions (one per minor, using the highest possible patch)
 * that the compatibility check is evaluated against.
 *
 * @return string[] The PHP versions, e.g. ["7.0.99", ..., "9.9.99"]
 */
function buildKnownPhpVersions(): array
{
    $phpVersions = [];

    foreach ([7, 8, 9] as $phpMajor) {
        foreach (range(0, 9) as $phpMinor) {
            $phpVersions[] = "{$phpMajor}.{$phpMinor}.99";
        }
    }

    return $phpVersions;
}

/**
 * Fetches every stable release of a package from the Packagist v2 metadata API.
 *
 * @param string $packageName The package name, e.g. "vendor/package"
 * @param VersionParser $versionParser The version parser used to detect stability
 * @return array<string, string> The PHP constraint of each release, keyed by its normalized version
 */
function fetchStableReleases(string $packageName, VersionParser $versionParser): array
{
    $metadataContents = @file_get_contents("https://repo.packagist.org/p2/{$packageName}.json");

    if ($metadataContents === false) {
        return [];
    }

    $metadata = json_decode($metadataContents, true, 512, JSON_THROW_ON_ERROR);
    $minifiedReleases = $metadata['packages'][$packageName] ?? [];
    $expandedRelease = [];
    $stableReleases = [];

    // Packagist minifies metadata: every release only lists fields that differ from the previous one
    foreach ($minifiedReleases as $minifiedRelease) {
        foreach ($minifiedRelease as $fieldName => $fieldValue) {
            if ($fieldValue === '__unset') {
                unset($expandedRelease[$fieldName]);
            } else {
                $expandedRelease[$fieldName] = $fieldValue;
            }
        }

        if (VersionParser::parseStability($expandedRelease['version']) !== 'stable') {
            continue;
        }

        $normalizedVersion = $versionParser->normalize($expandedRelease['version']);
        $stableReleases[$normalizedVersion] = $expandedRelease['require']['php'] ?? '*';
    }

    return $stableReleases;
}

/**
 * Bumps a single constraint branch (one side of an "||") to the newest compatible minor release.
 *
 * Only simple constraints such as "^1.2", "~v1.2", "^1.2.3" or ">=1.2" are handled, and 0.x
 * branches are left untouched because their minor releases are breaking by semver definition.
 *
 * @param string $constraintBranch The constraint branch, e.g. "~v1.37"
 * @param array<string, string> $packageReleases The PHP constraint of each release, keyed by normalized version
 * @param string[] $targetPhpVersions The PHP versions allowed by the project's own "php" constraint
 * @param VersionParser $versionParser The version parser
 * @return string|null The bumped constraint branch, or null when no newer compatible minor exists
 */
function bumpConstraintBranch(
    string $constraintBranch,
    array $packageReleases,
    array $targetPhpVersions,
    VersionParser $versionParser
): ?string {
    if (!preg_match('/^(\^|~|>=)?(v?)(\d+)(?:\.(\d+))?(?:\.(\d+))?$/', $constraintBranch, $constraintParts)) {
        return null;
    }

    [, $constraintOperator, $versionPrefix, $currentMajor] = $constraintParts;
    $currentMinor = (int) ($constraintParts[4] ?? 0);
    $componentCount = count(array_filter(array_slice($constraintParts, 3), static fn (string $part): bool => $part !== ''));

    if ((int) $currentMajor === 0) {
        return null;
    }

    // The currently declared release is the lowest one matching the branch
    $matchingReleases = Semver::sort(array_keys(array_filter(
        $packageReleases,
        static fn (string $phpConstraint, string $version): bool => Semver::satisfies($version, $constraintBranch),
        ARRAY_FILTER_USE_BOTH
    )));

    if ($matchingReleases === []) {
        return null;
    }

    $currentlySupportedPhpVersions = filterSupportedPhpVersions(
        $packageReleases[$matchingReleases[0]],
        $targetPhpVersions,
        $versionParser
    );

    $compatibleReleases = [];

    foreach ($packageReleases as $version => $phpConstraint) {
        [$releaseMajor, $releaseMinor] = array_map('intval', explode('.', $version));

        if ($releaseMajor !== (int) $currentMajor || $releaseMinor <= $currentMinor) {
            continue;
        }

        $supportedPhpVersions = filterSupportedPhpVersions($phpConstraint, $targetPhpVersions, $versionParser);

        // Never drop support for a PHP version that the current release supports
        if (array_diff($currentlySupportedPhpVersions, $supportedPhpVersions) === []) {
            $compatibleReleases[] = $version;
        }
    }

    if ($compatibleReleases === []) {
        return null;
    }

    // Pick the newest minor, then its lowest compatible patch as the new floor
    $compatibleReleases = Semver::sort($compatibleReleases);
    $newestMinor = (int) explode('.', end($compatibleReleases))[1];

    foreach ($compatibleReleases as $version) {
        $versionComponents = array_map('intval', explode('.', $version));

        if ($versionComponents[1] === $newestMinor) {
            $newComponents = array_slice($versionComponents, 0, max($componentCount, 2));

            return $constraintOperator . $versionPrefix . implode('.', $newComponents);
        }
    }

    return null;
}

/**
 * Filters the target PHP versions down to the ones allowed by a release's PHP constraint.
 *
 * @param string $phpConstraint The release's PHP constraint, e.g. ">=7.2"
 * @param string[] $targetPhpVersions The PHP versions allowed by the project
 * @param VersionParser $versionParser The version parser
 * @return string[] The supported PHP versions
 */
function filterSupportedPhpVersions(
    string $phpConstraint,
    array $targetPhpVersions,
    VersionParser $versionParser
): array {
    $parsedPhpConstraint = $versionParser->parseConstraints($phpConstraint);

    return array_values(array_filter(
        $targetPhpVersions,
        static fn (string $phpVersion): bool => $parsedPhpConstraint->matches($versionParser->parseConstraints($phpVersion))
    ));
}
