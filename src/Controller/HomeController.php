<?php

namespace Controller ;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class HomeController
{
    protected $logger ;
    protected $renderer ;
    protected $csrf ;
    protected $config ;
    
    protected $apiKey = 'an7nvfzojv5wa96dsga5nk8w';
    
    public function __construct($logger, $renderer, $csrf, $config)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        /** @var \Slim\Csrf\Guard csrf */
        $this->csrf = $csrf;
        $this->config = $config;
    }
    
    public function index(Request $request, Response $response, $args)
    {
        // CSRF
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $args['csrfNameKey'] = $nameKey;
        $args['csrfValueKey'] = $valueKey;
        $args['csrfName'] = $this->csrf->getTokenName();
        $args['csrfValue'] = $this->csrf->getTokenValue();
        // render view
        $res = $this->renderer->render($response, 'index.phtml', $args);
        return $res;
    }
    
    public function post(Request $request, Response $response, $args)
    {
        $startTime = microtime(true);
        if (false === $request->getAttribute('csrfStatus')) {
            die('Erreur de Csrf');
        } else {
        	// Vérification des répertoires
	        if (!is_dir($this->config['tmpPath']) && !mkdir($this->config['tmpPath'], 0777)) {
		        die('Echec lors de la création du répertoire temporaire...');
	        }
            // Get post data
            $postVars = $request->getParsedBody();
            $row = (int) $postVars['row'];
            $col = (int) $postVars['col'];
            $nbRow = (int) $postVars['nbrows'];
            $nbCol = (int) $postVars['nbcols'];
            // Try to get personnal key from geoportail
            // 1. On charge une page avec la carte
            $html = file_get_contents('https://www.geoportail.gouv.fr/carte');
            // 2. On cherche le fichier js qui contiendra la clef
            preg_match('/portail-front-[a-f0-9]+\.js/', $html, $matches);
            if (count($matches) !== 1) {
                die ('Erreur lors de la récupération de la clef API Géoportail (fichier js non trouvé).');
            }
            // 3. On charge le fichier js
            $jsFilename = 'https://www.geoportail.gouv.fr/assets/' . $matches[0];
            $js = file_get_contents($jsFilename);
            // 4. On y cherche la clef
            $pattern = 'https://wxs.ign.fr/' ;
            $pos = strpos($js, $pattern);
            if ($pos === false) {
                die ('Erreur lors de la récupération de la clef API Géoportail (clef dans le js non trouvée 1/2).');
            }
            $pos += strlen($pattern);
            $end = strpos($js, '/', $pos);
            if ($end === false) {
                die ('Erreur lors de la récupération de la clef API Géoportail (clef dans le js non trouvée 2/2).');
            }
           
            $this->apiKey = substr($js, $pos, $end - $pos);
	        $this->logger->addInfo("APIKey trouvée : ". $this->apiKey);
            
            // Create images urls
            $images = [] ;
            for ($i = 0; $i < $nbRow ; $i++ ) {
                for ($j = 0 ; $j < $nbCol ; $j++) {
                    $images[] = [
                        'row' => $i,
                        'col' => $j,
                        'url' => 'http://wxs.ign.fr/'.$this->apiKey.'/geoportail/wmts?SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=ORTHOIMAGERY.ORTHOPHOTOS&STYLE=normal&TILEMATRIXSET=PM&TILEMATRIX=19&TILEROW='
                        . ($row + $i) . '&TILECOL=' . ($col + $j) . '&FORMAT=image%2Fjpeg'
                    ];
                }
            }
            $config = [
                'todo' => $images,
                'done' => [],
                'rows' => $nbRow,
                'cols' => $nbCol,
                'times' => [
                    'config' => microtime(true) - $startTime,
                    'download' => 0
                ]
            ];
            // Write config file
            file_put_contents($this->config['configFile'], json_encode($config, JSON_PRETTY_PRINT));

            // Redirect to Download page after some seconds
            $seconds = rand(5,15);
            $response = $response->withHeader('Refresh', $seconds. ';URL=/download');
                        
            return $this->renderer->render($response, 'configuration.phtml', ['apiKey' => $this->apiKey]);
        }
    }
    
    public function download(Request $request, Response $response, $args)
    {
        // Read config file
        $images = json_decode(file_get_contents($this->config['configFile']));
        
        //echo('<pre>'); var_dump($images);
        $startTime = microtime(true);

        // pour faire croire au serveur qu'on est un vrai navigateur, sinon il nous jette.
        $context = stream_context_create([
            "http" => [
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
            ]
        ]);
        
        while (count ($images->todo)> 0 && microtime(true)-$startTime < 2) {
            // Download the image to process
            $image = $images->todo[0];
            
            $fileContents = file_get_contents($image->url, false, $context);
            $filename = 'img-'.$image->row.'-'.$image->col.'.jpg' ;
            
            file_put_contents($this->config['tmpPath'].$filename, $fileContents);
            $images->done[] = [
                'row' => $image->row,
                'col' => $image->col,
                'filename' => $filename
            ];
            unset ($images->todo[0]);
            $images->todo = array_values($images->todo);
        }
        
        // Write config file
        $images->times->download = $images->times->download + microtime(true) - $startTime;
        file_put_contents($this->config['configFile'], json_encode($images, JSON_PRETTY_PRINT));
        
        // Reload page after some seconds
        $seconds = rand(2, 7);
        $redirectUrl = '/download';
        // Si on a encore des images à faire
        if (count($images->todo) === 0){
            $redirectUrl = '/merge';
        }

        $response = $response->withHeader('Refresh', $seconds. ';URL='. $redirectUrl);
        return $this->renderer->render($response, 'download.phtml', [
            'nbDone' => count($images->done),
            'nbTotal' => count($images->done) + count($images->todo),
            'seconds' => $seconds,
            'downloadTime' => $images->times->download
        ]);
    }
    
    public function merge(Request $request, Response $response, $args)
    {
        // Read config file
        $images = json_decode(file_get_contents($this->config['configFile']));
        $bigImage = imagecreatetruecolor(256 * $images->cols, 256 * $images->rows);
        for ($i = 0; $i < $images->rows ; $i++ ) {
            for ($j = 0 ; $j < $images->cols ; $j++) {
                $imageName = 'img-'.$i.'-'.$j.'.jpg ' ;

                $img = imagecreatefromjpeg($this->config['tmpPath'].$imageName);
                if(!$img){
                    echo 'Failed' ; die;
                }
                imagecopy($bigImage, $img, 256 * $j, 256 * $i, 0, 0, 256, 256);
                imagedestroy($img);
            }
        }
        imagepng ($bigImage, 'bigImage.png');
        imagedestroy($bigImage);

	    $this->logger->addInfo("Image créée (". $images->rows .'x'. $images->cols.')');
        // Afficher la page final
        return $this->renderer->render($response, 'merge.phtml');
    }
    

}