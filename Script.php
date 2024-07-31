<?php
require_once('vendor/autoload.php');

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\NoSuchElementException;


$searchTerm = $argv[1] ?? 'abc';

$host = 'http://localhost:4444';
$capabilities = DesiredCapabilities::chrome();

$driver = RemoteWebDriver::create($host, $capabilities);

$driver->get('https://search.ipaustralia.gov.au/trademarks/search/advanced');

$divElement = $driver->findElement(WebDriverBy::id('_wordSearchTerms1'));
$inputElement = $divElement->findElement(WebDriverBy::tagName('input'));
$inputElement->sendKeys($searchTerm);

$searchElement = $driver->findElement(WebDriverBy::id('qa-search-submit'));
$searchElement->click();

$timeout = 10;
$startTime = time();
while (true) {
    try {
        $table = $driver->findElement(WebDriverBy::id('resultsTable'));
        break;
    } catch (NoSuchElementException $e) {
        if (time() - $startTime > $timeout) {
            die('Timed out waiting for results table');
        }
        sleep(1);
    }
}

$jsonArray = [];

do {
    $rows = $table->findElements(WebDriverBy::tagName('tr'));

    foreach ($rows as $row) {
        $data = [];
        try {

            $numberCell = $row->findElement(WebDriverBy::className('number'));
            $data['number'] = trim($numberCell->getText());
        } catch (NoSuchElementException $exception) {
            $data['number'] = null;
        }

        try {
            $imageElement = $row->findElement(WebDriverBy::tagName('img'));
            $data['url_logo'] = trim($imageElement->getAttribute('src'));
        } catch (NoSuchElementException $exception) {
            $data['url_logo'] = null;
        }

        try {
            $nameCell = $row->findElement(WebDriverBy::className('words'));
            $data['name'] = trim($nameCell->getText());
        } catch (NoSuchElementException $exception) {
            $data['name'] = null;
        }

        try {
            $classCell = $row->findElement(WebDriverBy::className('classes'));
            $data['class'] = trim($classCell->getText());
        } catch (NoSuchElementException $exception) {
            $data['class'] = null;
        }

        try {
            $statusCell = $row->findElement(WebDriverBy::className('status'));
            $data['status'] = trim($statusCell->getText());
        } catch (NoSuchElementException $exception) {
            $data['status'] = null;
        }

        try {
            $linkElement = $row->findElement(WebDriverBy::className('number'))->findElement(WebDriverBy::tagName('a'));
            $data['url_details_page'] = trim($linkElement->getAttribute('href'));
        } catch (NoSuchElementException $exception) {
            $data['url_details_page'] = null;
        }

        $jsonArray[] = $data;
    }

    try {
        $nextPageButton = $driver->findElement(WebDriverBy::className('js-nav-next-page'));
        $nextPageButton->click();
        sleep(3);
    } catch (NoSuchElementException $e) {
        break;
    }

    $table = $driver->findElement(WebDriverBy::id('resultsTable'));

} while (true);

echo json_encode($jsonArray, JSON_PRETTY_PRINT) . PHP_EOL;

$driver->quit();
?>
