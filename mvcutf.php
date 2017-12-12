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
            $model = new Model();
            $this->paragraphs = $model->checkParagraphNumbers($this->paragraphs);
            $this->pageData = $this->model->getPageData($this->page);
            $this->lastPage = $this->model->getLastPage();
            var_dump($this->pageData);


        }
    }

    class Model{
        private $paragraphs = 1;
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

        private function countLastPage(array $messagesArray): int
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

            echo "привет";

            $text = mb_convert_encoding($text, 'utf-8', 'CP1251');

            return explode("\r\n", $text);

        }
    }

    $controller = new Controller();
	$controller->run();

/*
	function controller()
    {

        $paragraphs = isset($_GET['parag'])?intval($_GET['parag']):1;

        $paragraphs = checkParagraphNumbers(FIRST_PARAGRAPHS_COUNT, QANTITY_PARAGRAPH_NUMBERS, $paragraphs);

        $page = isset($_GET['page'])?intval($_GET['page']):1;

        $pageData = model($page, $lastPage, $paragraphs);

        view($pageData, $page, $lastPage, $paragraphs);

    }

	function model(int $page, &$lastPage, int $paragraphs): array
    {

        $messagesArray = readData();

        $lastPage = countLastPage($messagesArray, $paragraphs);

        if (!checkPageNumber($page, $lastPage))
            die('Страница ненайдена!');

        return extractPageData($messagesArray, $page, $paragraphs);

    }

	function readData(): array
    {

        require 'text.php';

        $text = mb_convert_encoding($text, 'utf-8', 'CP1251');

        return explode("\r\n", $text);

    }

	function countLastPage(array $messagesArray, int $paragraphs): int
    {

        return ceil( sizeof($messagesArray) / $paragraphs );

    }

	function checkPageNumber(int $page, int $lastPage): bool
    {

        return ($page >= 1 && $page <= $lastPage);

    }

	function extractPageData(array $messagesArray, int $page, int $paragraphs): array
    {

        $first = ($page - 1) * $paragraphs;

        return array_slice($messagesArray, $first, $paragraphs);

    }

	function view(array $pageData, int $page, int $lastPage, int $paragraphs): void
    {

        renderPageNumbers(FIRST_PARAGRAPHS_COUNT, QANTITY_PARAGRAPH_NUMBERS, $paragraphs );

        renderData($pageData);

        renderPagination($page, $lastPage, $paragraphs);

    }

	function renderData(array $pageData): void
    {

        foreach($pageData as $messages)
        {

            $quantityWords = countWordsLetters($messages);

            $messages = markFirstLetter($messages);

            $messages = setColor($messages);

            echo "<p>$messages</p>$quantityWords";

        }

    }

	function renderPagination(int $page, int $lastPage, int $paragraphs): void
    {

        for($i = 1; $i <= $lastPage; $i++)
            if($i != $page)
                echo "<a href=\"ksr2b.php?page=$i&parag=$paragraphs\"> $i&nbsp; </a>";
            else
                echo " $i ";

    }

	function checkParagraphNumbers(int $firstParagrapsCount, int $quantity ,int $paragraps): int
    {

        $maxParagraph = calculateMaxParagraphs($firstParagrapsCount, $quantity);

        if($paragraps <= $firstParagrapsCount)
            $paragraps = $firstParagrapsCount;
        else
        {
            if($paragraps > $maxParagraph)
                $paragraps = $maxParagraph;
            else
                for ($i = $firstParagrapsCount; $i < $maxParagraph; $i *= 2)
                {

                    if($paragraps > $i && $paragraps <= $i*2)
                        $paragraps = $i*2;
                }
        }

        return $paragraps;

    }

	function calculateMaxParagraphs(int $firstParagrapsCount, int $quantity): int
    {

        return $firstParagrapsCount * pow(2,($quantity-1));

    }

	function renderPageNumbers(int $firstParagrapsCount, int $quantity, int $paragraps )
    {

        $maxParagraph = calculateMaxParagraphs($firstParagrapsCount, $quantity);

        echo 'Колличество абзацев: ';

        for ($i = $firstParagrapsCount; $i <= $maxParagraph; $i *= 2)
        {
            if ($i!=$paragraps)
                echo "<a href=\"ksr2b.php?parag=$i\"> $i&nbsp; </a> ";
            else
                echo ' ',$i, ' ';

            if ($i < $maxParagraph)
                echo '|';
            else
                echo '</br>';
        }

    }

	function countWordsLetters(string $dataString): string
    {

        $regexp = "/[А-Яа-яЁёa-z]+[\-]{0,1}[А-Яа-яЁёa-z]*///iu";

       // $dataString = strip_tags($dataString);

       // $amountWords = preg_match_all($regexp, $dataString, $out, PREG_PATTERN_ORDER);

       // $amountLetters = iconv_strlen($dataString,'UTF-8');

       // return "Колличество слов: $amountWords<br>Общая длина абзаца: $amountLetters<br>";
/*
    }

	function markFirstLetter(string $dataString): string
    {

        $regexp = "/(^|[?!.]\s*)([А-Яа-яЁёa-z])/iu";

        $replacement = "$1<b>$2</b>";

        return preg_replace($regexp, $replacement, $dataString);

    }

    function makeRandomColor(): string
    {

        $colors = ["brown","green","blue","red"];

        $index = mt_rand(0, (sizeof($colors)-1));

        return $colors[$index];

    }

	function setColor(string $dataString): string
    {

        $regexp = '/[\<a-z\>]*([HPJ])[\<\/a-z\>]*(TML|HP|SP.NET|SP|ava)/i';

        $color = makeRandomColor();

        $replacement ="<span style=\"color: $color;\">$1$2</span>";

        //$replacement ="<b>$1</b>";

        return preg_replace($regexp, $replacement, $dataString);

    }
*/