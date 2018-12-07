<?php

namespace anime\forms;

use anime\ui\UXIDTableColumn;
use anime\ui\UXPreloader;
use php\gui\effect\UXEffectPipeline;
use php\gui\layout\UXAnchorPane;
use php\gui\UXApplication;
use php\gui\UXButton;
use php\gui\UXForm;
use php\gui\UXLoader;
use php\gui\UXTableCell;
use php\gui\UXTableColumn;
use php\gui\UXTableView;
use php\gui\UXTextField;
use php\io\Stream;
use php\lang\Thread;
use php\lib\fs;
use php\lib\str;

/**
 * Class MainForm
 * @package anime\forms
 *
 * @property UXButton btn_sync
 * @property UXTableView table
 * @property UXTextField search_text
 * @property UXButton search_btn
 */
class MainForm extends UXForm
{
    private $dataBase;

    /**
     * @var UXPreloader
     */
    private $preloader;

    public function __construct()
    {
        parent::__construct();

        $this->layout = (new UXLoader())->load(Stream::of("res://anime/forms/MainForm.fxml"));
        $this->preloader = new UXPreloader($this->layout, "Checking Data Base ...");
        $this->preloader->show();

        $this->table->columns->addAll([
            $view = new UXIDTableColumn("view", "View", false),
            new UXIDTableColumn("title", "Title"),
            new UXIDTableColumn("type", "Type"),
            new UXIDTableColumn("episodes", "Episodes")
        ]);

        $view->minWidth = $view->minWidth = 70;

        $this->btn_sync->on("action", function () {
            fs::delete("./database.json");
            $this->preloader->show();
            $this->update();
        });

        $this->search_btn->on("click", function () {
            $this->preloader->show();
            $this->preloaderText("Searching ....");
            (new Thread(function () {
                 $this->updateTable();
                 $this->hidePreloader();
            }))->start();
        });
    }

    public function show()
    {
        parent::show();
        $this->update();
    }

    public function updateTable() {
        $this->table->items->clear();

        foreach ($this->dataBase["data"] as $data) {
            $btn = new UXButton("View");
            $btn->on("click", function () use ($data) {
                $view = new ViewInfo($data);
                $view->show();
            });
            try {
                if ($this->search_text->text != null) {

                    if (str::pos($data["title"], $this->search_text->text) > -1) $this->addItem($data, $btn);

                } else {
                    $this->addItem($data, $btn);
                }
            } catch (\Throwable $exception) {
                echo "Отпало по неизвестной ошибке: " . $data["title"] . " :C \n";
            }
        }
    }

    private function addItem(array $data, UXButton $button) {
        UXApplication::runLater(function () use ($data, $button) {
            $this->table->items->add([
                "view"     => $button,
                "title"    => $data["title"],
                "type"     => $data["type"],
                "episodes" => $data["episodes"]
            ]);
        });
    }

    public function update() {
        $this->table->items->clear();
        (new Thread(function () {
            $this->preloaderText("Checking Data Base ...");
            if (!fs::isFile("./database.json")) {
                $this->preloaderText("Downloading Anime Data Base ...");
                Stream::putContents("./database.json", Stream::getContents("https://raw.githubusercontent.com/manami-project/anime-offline-database/master/anime-offline-database.json"));
                $this->preloaderText("Done ...");
            }

            $this->preloaderText("Parsing data base ...");
            $this->dataBase = json_decode(Stream::getContents("./database.json"), true);
            $this->updateTable();
            $this->preloaderText("Done ...");
            $this->hidePreloader();

        }))->start();
    }

    private function preloaderText(string $text) {
        UXApplication::runLater(function () use ($text) { $this->preloader->setText($text); });
    }

    private function hidePreloader() {
        UXApplication::runLater(function () { $this->preloader->hide(); });
    }
}