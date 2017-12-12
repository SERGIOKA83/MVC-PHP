<?php
	define('QANTITY_PARAGRAPH_NUMBERS', 4);

	define('FIRST_PARAGRAPHS_COUNT', 5);

	class Controller{
	    private $paragraphs = 1;
	    private $page = 1;
        private $pageData;
        private $lastPage;
        private $model;
        private $view;

	    function run()
        {

            if(isset($_GET['parag'])) $this->paragraphs = intval($_GET['parag']);

            if(isset($_GET['page'])) $this->page = intval($_GET['page']);

            $this->model = new Model();

            $this->paragraphs = $this->model->checkParagraphNumbers($this->paragraphs);

            $this->pageData = $this->model->getPageData($this->page);

            $this->lastPage = $this->model->getLastPage();

           // var_dump($this->pageData);

            $this->view = new View($this->lastPage, $this->paragraphs);

            $this->view->render($this->pageData, $this->page);

        }
    }

    class Model{
        private $paragraphs;
        private $page;
        private $lastPage;

        function checkParagraphNumbers(int $paragraps): int
        {

            $maxParagraph = $this->calculateMaxParagraphs();

            if($paragraps <= FIRST_PARAGRAPHS_COUNT)
                $paragraps = FIRST_PARAGRAPHS_COUNT;
            else
            {
                if($paragraps > $maxParagraph)
                    $paragraps = $maxParagraph;
                else
                    for ($i = FIRST_PARAGRAPHS_COUNT; $i < $maxParagraph; $i *= 2)
                    {

                        if($paragraps > $i && $paragraps <= $i*2)
                            $paragraps = $i*2;
                    }
            }
            $this->paragraphs = $paragraps;
            return $paragraps;

        }

        function getPageData(int $page): array
        {
            $this->page = $page;
            $messagesArray = $this->readData();

            $this->countLastPage($messagesArray);

            if (!$this->checkPageNumber())
                die('Страница ненайдена!');

            return $this->extractPageData($messagesArray);

        }

        private function countLastPage(array $messagesArray): void
        {

            $this->lastPage = ceil( sizeof($messagesArray) / $this->paragraphs );

        }

        private function checkPageNumber(): bool
        {

            return ($this->page >= 1 && $this->page <= $this->lastPage);

        }

        private function extractPageData(array $messagesArray): array
        {

            $first = ($this->page - 1) * $this->paragraphs;

            return array_slice($messagesArray, $first, $this->paragraphs);

        }

        function getLastPage(): int
        {
            return $this->lastPage;
        }

        private function calculateMaxParagraphs(): int
        {

            return FIRST_PARAGRAPHS_COUNT * pow(2,(QANTITY_PARAGRAPH_NUMBERS-1));

        }

        private function readData(): array
        {

            require 'text.php';

            $text = mb_convert_encoding($text, 'utf-8', 'CP1251');

            return explode("\r\n", $text);

        }
    }

class View{
    private $pageData;
    private $paragraphs;
    private $page;
    private $lastPage;

    function __construct(int $lastPage, int $paragraphs)
    {
        $this->lastPage = $lastPage;
        $this->paragraphs = $paragraphs;
    }
    function render(array $pageData, int $page)
    {
        $this->pageData = $pageData;

        $this->page = $page;

        $this->renderPageNumbers();

        $this->renderData();

        $this->renderPagination();
    }

   private function renderPageNumbers(): void
    {

        $maxParagraph = $this->calculateMaxParagraphs();

        echo 'Колличество абзацев: ';

        for ($i = FIRST_PARAGRAPHS_COUNT; $i <= $maxParagraph; $i *= 2)
        {
            if ($i!=$this->paragraphs)
                echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?parag=$i\"> $i&nbsp; </a> ";
            else
                echo ' ',$i, ' ';

            if ($i < $maxParagraph)
                echo '|';
            else
                echo '</br>';
        }

    }

   private function renderData(): void
    {

        foreach($this->pageData as $messages)
        {

            $quantityWords = $this->countWordsLetters($messages);

            $messages = $this->markFirstLetter($messages);

            $messages = $this->setColor($messages);

            echo "<p>$messages</p>$quantityWords";

        }

    }

    private function renderPagination(): void
    {

        for($i = 1; $i <= $this->lastPage; $i++)
            if($i != $this->page)
                echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?page=$i&parag=$this->paragraphs\"> $i&nbsp; </a>";
            else
                echo " $i ";

    }

    private function calculateMaxParagraphs(): int
    {

        return FIRST_PARAGRAPHS_COUNT * pow(2,(QANTITY_PARAGRAPH_NUMBERS-1));

    }

    private function countWordsLetters(string $messages): string
    {

        $regexp = "/[А-Яа-яЁёa-z]+[\-]{0,1}[А-Яа-яЁёa-z]*/iu";

        $messages = strip_tags($messages);

        $amountWords = preg_match_all($regexp, $messages, $out, PREG_PATTERN_ORDER);

        $amountLetters = iconv_strlen($messages,'UTF-8');

        return "Колличество слов: $amountWords<br>Общая длина абзаца: $amountLetters<br>";

    }

    private function markFirstLetter(string $messages): string
    {

        $regexp = "/(^|[?!.]\s*)([А-Яа-яЁёa-z])/iu";

        $replacement = "$1<b>$2</b>";

        return preg_replace($regexp, $replacement, $messages);

    }

    private function setColor(string $messages): string
    {

        $regexp = '/[\<a-z\>]*([HPJ])[\<\/a-z\>]*(TML|HP|SP.NET|SP|ava)/i';

        $color = $this->makeRandomColor();

        $replacement ="<span style=\"color: $color;\">$1$2</span>";

        //$replacement ="<b>$1</b>";

        return preg_replace($regexp, $replacement, $messages);

    }

    private function makeRandomColor(): string
    {

        $colors = ["brown","green","blue","red"];

        $index = mt_rand(0, (sizeof($colors)-1));

        return $colors[$index];

    }
}
    $controller = new Controller();
	$controller->run();