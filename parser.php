<?php
    if(isset($argv[1])){
        $json_file = $argv[1];
    }else{
        $json_file = 'data.json';
    }
    
    require_once 'twig/lib/Twig/Autoloader.php';
    Twig_Autoloader::register();
     
    $loader = new Twig_Loader_Filesystem('templates');
    $twig = new Twig_Environment($loader, array(
        // 'cache' => 'compilation_cache',
    ));
    
    if(is_readable($json_file) === false){
        exit('The file ' . $json_file . '" does not exist, or is not readable. Check the documentation on Github.' . "\n");
    } 
    if(!$dataset = json_decode(file_get_contents($json_file), true)){
        exit('Could not load JSON file "' . $json_file . '". Syntax error?' . "\n");
    }

    $defaults = array_shift($dataset);

    $i = 0; # counter
    
    /**
     * Used to get values from the JSON dataset based on key, with fallback to
     * default if nothing is specified in the JSON dataset for the host.
     * @param $hostname Hostname to lookup
     * @poram $key Key to search for
     * @return string|bool Content from JSON dataset
     */
    function extract_json($hostname, $key){
        global $dataset;
        global $defaults;
        if(isset($dataset[$hostname][$key])){
            return $dataset[$hostname][$key];
        }else{
            return $defaults[$key];
        }
    }
    
    foreach($dataset as $hostname => $v){
        $context = array(
            'root' => extract_json($hostname, 'root'),
            'enforce_https' => extract_json($hostname, 'enforce_https'),
            'ssl_parent_domain' => extract_json($hostname, 'ssl_parent_domain'),
            'aliases' => extract_json($hostname, 'aliases'),
            'custom_config' => extract_json($hostname, 'custom_config'),
            'allow_override_on_root' => extract_json($hostname, 'allow_override_on_root')
        );
        if(file_put_contents('generated-vhost-configs/' . $hostname . '.conf', $twig->render('vhost.twig', $context)) !== false){
            $i++;
        }
    }
    exit('finished - wrote ' . (int) $i . ' .conf files' . "\n");
?>
