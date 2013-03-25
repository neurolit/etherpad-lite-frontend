<?php

var $config;

if (file_exists(__DIR__."/../conf/app.yml")) {
    $config = Yaml::parse(__DIR__."/../conf/app.yml");
} else {
    $config = Yaml::parse(__DIR__."/../conf/app_default.yml");
}
