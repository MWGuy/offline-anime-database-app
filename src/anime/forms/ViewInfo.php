<?php

namespace anime\forms;

use php\gui\UXDesktop;
use php\gui\UXForm;
use php\gui\UXImage;
use php\gui\UXImageView;
use php\gui\UXLabel;
use php\gui\UXListView;
use php\gui\UXLoader;
use php\io\Stream;
use php\lib\fs;
use php\lib\str;

/**
 * Class ViewInfo
 * @package anime\forms
 *
 * @property UXImageView image
 * @property UXListView sources
 * @property UXLabel name
 * @property UXLabel type
 * @property UXLabel episodes
 * @property UXLabel synonyms
 */
class ViewInfo extends UXForm
{
    /**
     * @var array
     */
    private $data;

    /**
     * ViewInfo constructor.
     *
     * @param array $data
     * @throws \php\io\IOException
     */
    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;

        $this->layout = (new UXLoader())->load(Stream::of("res://anime/forms/ViewInfo.fxml"));

        $this->sources->on("click", function () {
            if ($this->sources->selectedItem) (new UXDesktop())->browse($this->sources->selectedItem);
        });
    }

    public function show() {
	$file = "./cache/". md5($this->data["title"]);

        if (!fs::isFile($file)) {
            fs::makeFile($file);
            Stream::putContents($file, Stream::getContents($this->data["picture"]));
        }

        $this->image->image = new UXImage(Stream::of($file));
        foreach ($this->data["sources"] as $source) $this->sources->items->add($source);

        $this->name->text = $this->data["title"];
        $this->type->text = "Type: " . $this->data["type"];
        $this->episodes->text = "Episodes: " . $this->data["episodes"];
        $this->synonyms->text = "Synonyms: " . str::join($this->data["synonyms"], ", ");
        $this->synonyms->wrapText = true;

        parent::show();
    }
}