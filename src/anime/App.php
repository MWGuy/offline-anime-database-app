<?php

namespace anime;


use anime\forms\MainForm;
use php\gui\UXApplication;

class App
{
    public function __construct() {

    }

    public function start() {
        UXApplication::launch(function () {
            (new MainForm())->show();
        });
    }
}