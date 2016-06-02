<?php
  if (file_exists('PHPDump/src/debug.php')) {
    require_once 'PHPDump/src/debug.php';
  }

  set_time_limit(900);

  $jsonFileName = 'htmlData.json';

  class htmlData {
    public $elements = [];
  }

  class htmlElement {
    public $categories = [];
    public $content = "";
    public $tagOmission = "";
    public $attributes = [];
    public $interfaces = [];
  }


  function formatAttributeName($name) {
    // Remove hash sign at the beginning
    $formattedName = substr($name, 1);

    // Replace underscores by spaces
    $formattedName = str_replace('_', ' ', $formattedName);

    // Fix casing
    $formattedName = lcfirst(ucwords($formattedName));

    // Remove spaces and the word 'Attributes'
    $formattedName = str_replace(['Attributes', ' '], '', $formattedName);

    // Append 'Attributes'
    $formattedName .= 'Attributes';
     
    return $formattedName;
  }


  $htmlData = new htmlData();

  $locales = [
    'en-US' => [
      'permittedContent' => 'Permitted content'
    ]/*,
    'ar' => [
    ],
    'bn-BD' => [
      'permittedContent' => 'Permitted content'
    ],
    'ca' => [
    ],
    'cs' => [
    ],
    'de' => [
      'permittedContent' => 'Erlaubter Inhalt'
    ],
    'es' => [
      'permittedContent' => 'Contenido permitido'
    ],
    'fa' => [
    ],
    'fr' => [
      'permittedContent' => 'Contenu autorisé|Contenu authorisé|Contenu permis|Contenu permit|Contenu'
    ],
    'he' => [
    ],
    'hu' => [
    ],
    'id' => [
    ],
    'it' => [
    ],
    'ja' => [
      'permittedContent' => '許可された内容|利用可能な中身'
    ],
    'ko' => [
      'permittedContent' => 'Permitted content'
    ],
    'ms' => [
    ],
    'nl' => [
    ],
    'pl' => [
    ],
    'pt-BR' => [
      'permittedContent' => 'Conteúdo permitido|Contepudo permitido'
    ],
    'pt-PT' => [
    ],
    'ro' => [
    ],
    'ru' => [
      'permittedContent' => 'Permitted content'
    ],
    'tr' => [
    ],
    'uk' => [
    ],
    'vi' => [
    ],
    'zh-CN' => [
      'permittedContent' => '允许的内容物|允许的内容|允许的子元素|允许内容'
    ],
    'zh-TW' => [
    ]*/
  ];
  $elementReferenceURL = 'https://developer.mozilla.org/%s/docs/Web/HTML/Element/';
  $outputFolder = 'output';

  if (!file_exists($outputFolder)) {
    mkdir($outputFolder);
  }
  foreach ($locales as $locale => $localeItems) {
    $filePath = $outputFolder . '/elementReference.' . $locale . '.html';
    if (isset($_GET['refresh']) || !file_exists($filePath)) {
      $fetchLocation = sprintf($elementReferenceURL, $locale);
    } else {
      $fetchLocation = $filePath;
    }

    $response = file_get_contents($fetchLocation);

    if (isset($_GET['refresh']) || !file_exists($filePath)) {
      file_put_contents($filePath, $response);
    }

    //if (preg_match('/<table class="index.*?">.+?<\/div>/s', $response, $indexMatch)) {
    preg_match_all('/<table class="standard-table">.+?<\/table>/s', $response, $categoryMatches);

    $elementURLPaths = [];
    foreach ($categoryMatches[0] as $index => $categoryMatch) {
      // Check which element pages to fetch
      preg_match_all('/<a\s+(?:\S+=".+?"\s+)*href="(.+?)"(?:\s\S+=".+?")*\s*>/', $categoryMatch, $categoryLinkMatches);
      $categoryURLPaths = [];
      foreach ($categoryLinkMatches[1] as $index => $categoryURLMatch) {
        if (preg_match('/\/Element\/[a-z0-9]+$/', $categoryURLMatch) && strpos($categoryLinkMatches[0][$index], 'class="new"') === false) {
          array_push($categoryURLPaths, $categoryURLMatch);
        }
      }
      $elementURLPaths = array_merge($elementURLPaths, $categoryURLPaths);
    }

    // Fetch each element page and parse it
    foreach ($elementURLPaths as $urlPaths) {
      preg_match('/.*\/(.+)$/', $urlPaths, $pageMatch);
      $element = $pageMatch[1];
      $filePath = $outputFolder . '/' . $element . '.' . $locale . '.html';
      if (isset($_GET['refresh']) || !file_exists($filePath)) {
        $fetchLocation = 'https://developer.mozilla.org' . $urlPaths;
      } else {
        $fetchLocation = $filePath;
      }

      if (!isset($htmlData->elements[$element])) {
        $htmlData->elements[$element] = new htmlElement();
      }

      $response = file_get_contents($fetchLocation);

      if (isset($_GET['refresh']) || !file_exists($filePath)) {
        file_put_contents($filePath, $response);
      }
    }
  }

  file_put_contents($jsonFileName, json_encode($htmlData, JSON_PRETTY_PRINT));

  dump($htmlData);
?>