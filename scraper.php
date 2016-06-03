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
    public $content = [];
    public $tagOmission = [];
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
      'contentCategories' => 'Content categories',
      'permittedContent' => 'Permitted content',
      'tagOmission' => 'Tag omission',
      'permittedParents' => 'Permitted parent elements',
      'domInterface' => 'DOM interface'
    ]/*,
    'ar' => [
    ],
    'bn-BD' => [
      'permittedContent' => 'Permitted content'
    ]*/,
    'ca' => [
      'contentCategories' => 'Content Categories de contingut',
      'permittedContent' => 'Contingut permès',
      'tagOmission' => 'Omissió de l\'etiqueta',
      'permittedParents' => 'Elements pares permesos',
      'domInterface' => 'Interfície DOM'
    ],
    'cs' => [
      'contentCategories' => 'Content categories',
      'permittedContent' => 'Permitted content',
      'tagOmission' => 'Tag omission',
      'permittedParents' => 'Permitted parent elements',
      'domInterface' => 'DOM interface'
    ]/*,
    'de' => [
      'permittedContent' => 'Erlaubter Inhalt'
    ]*/,
    'es' => [
      'contentCategories' => 'Content categories',
      'permittedContent' => 'Contenido permitido',
      'tagOmission' => 'Omisión de la etiqueta',
      'permittedParents' => 'Elementos padres permitidos',
      'domInterface' => 'Interfaz DOM'
    ],
    'fa' => [
      'contentCategories' => 'Content categories',
      'permittedContent' => 'Permitted content',
      'tagOmission' => 'Tag omission',
      'permittedParents' => 'Permitted parent elements',
      'domInterface' => 'DOM interface'
    ],
    'fr' => [
      'contentCategories' => 'Catégories de contenu',
      'permittedContent' => 'Contenu autorisé',
      'tagOmission' => 'Omission de balises',
      'permittedParents' => 'Éléments parents autorisés',
      'domInterface' => 'Interface DOM'
    ]/*,
    'he' => [
    ],
    'hu' => [
    ],
    'id' => [
    ],
    'it' => [
    ]*/,
    'ja' => [
      'contentCategories' => 'コンテンツカテゴリ',
      'permittedContent' => '許可された内容|利用可能な中身',
      'tagOmission' => 'タグの省略',
      'permittedParents' => '許可された親要素',
      'domInterface' => 'DOM インターフェイス'
    ],
    'ko' => [
      'contentCategories' => '컨텐트 카테고리',
      'permittedContent' => '허용된 컨텐트',
      'tagOmission' => '태그 생략',
      'permittedParents' => '허용된 부모 요소들',
      'domInterface' => 'DOM 인터페이스'
    ]/*,
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
    ]*/,
    'ru' => [
      'contentCategories' => 'Категории контента',
      'permittedContent' => 'Допустимое содержимое',
      'tagOmission' => 'Пропуск тегов',
      'permittedParents' => 'Допустимые родительские элементы',
      'domInterface' => 'Интерфейс DOM'
    ],
    'tr' => [
    ],
    'uk' => [
    ],
    'vi' => [
    ],
    'zh-CN' => [
      'contentCategories' => '内容类别',
      'permittedContent' => '允许内容',
      'tagOmission' => '标记省略',
      'permittedParents' => '允许的父元素',
      'domInterface' => 'DOM 接口'
    ]/*,
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

      if (preg_match('/<table class="(?:standard-table|properties)">.+?<\/table>|<ul class="htmlelt">.+?<\/ul>/s', $response, $infoTableMatches)) {
      	// Parse categories
        if ($locale === 'en-US' && preg_match('/(?:' . $locales['en-US']['contentCategories'] . '|' .
            $localeItems['contentCategories'] . ').+?<td>(.+?)<\/td>|(?:' . $locales['en-US']['contentCategories'] . '|' .
            $localeItems['contentCategories'] . ').+?<\/dfn>(.+?)<\/li>/is', $infoTableMatches[0], $categoriesMatches)) {
          $categories = explode(', ', $categoriesMatches[1]);
          $categories = array_map(function($category) {
            $category = preg_replace('/<.+?>/', '', $category);
            $words = explode(' ', $category);
            foreach ($words as $index => $word) {
              $words[$index] = ($index === 0) ? lcfirst($word) : ucfirst($word);
            }
            return str_replace('.', '', implode('', $words));
          }, $categories);
          $htmlData->elements[$element]->categories = $categories;
        }

        // Parse permitted content
        if (preg_match('/(?:' . $locales['en-US']['permittedContent'] . '|' .
          $localeItems['permittedContent'] . ').+?(?:<td>(.+?)<\/td>|<\/dfn>(.+?)<\/li>)/su', $infoTableMatches[0], $contentMatches)) {
            $htmlData->elements[$element]->content[$locale] = $contentMatches[1] !== '' ? $contentMatches[1] : $contentMatches[2];
        }

        // Parse tag omission
        if (preg_match('/(?:' . $locales['en-US']['tagOmission'] . '|' .
          $localeItems['tagOmission'] . ').+?(?:<td>(.+?)<\/td>|<\/dfn>(.+?)<\/li>)/su', $infoTableMatches[0], $contentMatches)) {
            $htmlData->elements[$element]->tagOmission[$locale] = $contentMatches[1] !== '' ? $contentMatches[1] : $contentMatches[2];
        }

        // Parse permitted parent elements
        if (preg_match('/(?:' . $locales['en-US']['permittedParents'] . '|' .
          $localeItems['permittedParents'] . ').+?(?:<td>(.+?)<\/td>|<\/dfn>(.+?)<\/li>)/su', $infoTableMatches[0], $contentMatches)) {
            $htmlData->elements[$element]->permittedParents[$locale] = $contentMatches[1] !== '' ? $contentMatches[1] : $contentMatches[2];
        }

        // Parse DOM interface
        if (preg_match('/(?:' . $locales['en-US']['domInterface'] . '|' .
          $localeItems['domInterface'] . ').+?(?:<td>(.+?)<\/td>|<\/dfn>(.+?)<\/li>)/su', $infoTableMatches[0], $contentMatches)) {
            $htmlData->elements[$element]->domInterface[$locale] = preg_replace('/<.+?(\s+\S+=".*?")*>/', '',
                $contentMatches[1] !== '' ? $contentMatches[1] : $contentMatches[2]);
        }
      }
    }
  }

  file_put_contents($jsonFileName, json_encode($htmlData, JSON_PRETTY_PRINT));

  dump($htmlData);
?>