<?php

include 'vendor/autoload.php';

include 'bootstrap.php';


$vendorDir = realpath(\Composer\InstalledVersions::getInstallPath('league/uri-polyfill'));


echo $vendorDir;