<?php
    use Silex\Application;
    use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\Yaml\Yaml;
    use Neoxygen\NeoClient\ClientBuilder;
    
    require __DIR__.'/vendor/autoload.php';
    define("ANDC", " and ");
    $app = new Application();
    
  
        $config = Yaml::parse(file_get_contents(__DIR__.'/config/config.yml'));
        $cnx = parse_url($config['neo4j_url']);
   
    
    $neo4j = ClientBuilder::create()
    ->addConnection('default', $cnx['scheme'], $cnx['host'], $cnx['port'], true, $cnx['user'], $cnx['pass'])
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20)
    ->build();
    
    $app->get('/', function () {
              return file_get_contents(__DIR__.'/static/index4.html');
              });
    
    $app->get('/graph', function (Request $request) use ($neo4j) {
              $sourceAITs='(?i).*';
              $targetAITs='(?i).*';
              $orgSource ='(?i).*';
              $orgTarget ='(?i).*';
              $sourceAITs = $request->get('sourceAITs');
              $targetAITs = $request->get('targetAITs');
              $sourceAITs = '(?i).*'.$sourceAITs.'.*';
              $targetAITs = '(?i).*'.$targetAITs.'.*';
              $orgSource = urldecode($request->get('sourceBU'));
              $adsSource = urldecode($request->get('sourceADS'));
              $orgTarget = urldecode($request->get('targetBU'));
              $adsTarget = urldecode($request->get('targetADS'));
              
              if($orgSource=="" || is_null($orgSource))
                $orgSource ='(?i).*';
              else
                $orgSource ='(?i).*'.$orgSource.'.*';
              if($orgTarget=="" || is_null($orgTarget) )
                $orgTarget ='(?i).*';
              else
                $orgTarget ='(?i).*'.$orgTarget.'.*';
              $params = [];
              $params = ['sources'=>$sourceAITs,
              'sourcesOrg'=>$orgSource,
              'targets'=>$targetAITs,
              'targetsOrg'=>$orgTarget];
              $query = 'MATCH pa=((m:System)-[r:DataFlow*..]-(p:System)) where m.name =~ {sources} and p.name=~ {targets} RETURN pa';
             
              $result = $neo4j->sendCypherQuery($query, $params)->getResult();
              
              $nodes = [];
              $edges = [];
              $nodesPositions = [];
              
              $i = 0;
              foreach ($result->getNodes() as $node){
              $prop = ($node->getLabel() === 'System') ? 'name' : 'name';
              $nodes[] = [
              'name' => $node->getProperty('name'),
              'title' => $node->getProperty($prop),
              'label' => $node->getLabel(),
              'Org' => $node->getProperty('Org'),
              'ADS' => $node->getProperty('isADS')
              ];
              $nodesPositions[$node->getId()] = $i;
              $i++;
              }
              
              foreach ($result->getRelationships() as $rel){
              $edges[] = [
              'source' => $nodesPositions[$rel->getStartNode()->getId()],
              'target' => $nodesPositions[$rel->getEndNode()->getId()]
              ];
              }
              
              $data = [
              'nodes' => $nodes,
              'links' => $edges
              ];
              //   print_r(json_decode($data));
              $response = new JsonResponse();
              $response->setData($data);
              
              return $response;
              });
    
    $app->get('/search', function (Request $request) use ($neo4j) {
              //create default search params
              $params = getSearchParams($request);
     //           print_r($params);echo '<br/>';
              $sources = $params['sources'];
              $targets = $params['targets'];
              $sourcesOrg = $params['sourcesOrg'];
              $targetsOrg = $params['targetsOrg'];
              $query = 'MATCH pa=((m:System)-[r:DataFlow*..]-(p:System)) where '.$sources.ANDC.$targets.ANDC.$sourcesOrg.ANDC.$targetsOrg.' RETURN pa';
           //   print($query);echo '<br/>';
              $result = $neo4j->sendCypherQuery($query, $params)->getResult();
              $systems = [];
              foreach ($result->getNodes() as $system){
              //  print_r($system->getProperty('name'));
              $systems[] = ['system' => $system->getProperties()];
              }
              
              //  print_r($systems);
              
              $response = new JsonResponse();
              $response->setData($systems);
              
              return $response;
              });
    
    $app->get('/system/{sysid}', function ($sysid) use ($neo4j) {
              $q = 'MATCH (m:System) WHERE m.SysID = 8388 RETURN m';
              $params = [];//['SysID' => $sysid];
              //         print_r($q);
              //       print_r($params);
              $result = $neo4j->sendCypherQuery($q, $params)->getResult();
              //     print_r("Success");
              //   print_r($result);
              $system = $result->getSingleNodeByLabel('System');
              // print_r($system);
              
              $sys[] = ['system'=>$system->getProperties()];
              //print_r($sys);
              
              $response = new JsonResponse();
              $response->setData($sys);
              
              return $response;
              });
    
    function getSearchParams($request){
        $sourceAITs='(?i).*';
        $targetAITs='(?i).*';
        $orgSource ='TRUE';
        $orgTarget ='TRUE';
        $adsSource='TRUE';
        $adsTarget = 'TRUE';
        $sourceAITs = urldecode($request->get('sourceAITs'));
        $targetAITs = urldecode($request->get('targetAITs'));
        $sourceAITs = ' m.name =~ "(?i).*'.$sourceAITs.'.*"';
        $targetAITs = ' p.name =~ "(?i).*'.$targetAITs.'.*"';
            $orgSource = ' m.Org in '.parseComp(urldecode($request->get('sourceBU')));
        $adsSource = urldecode($request->get('sourceADS'));
            $orgTarget = ' p.Org in '.parseComp(urldecode($request->get('targetBU')));
        $adsTarget = urldecode($request->get('targetADS'));
        
        $params = ['sources'=>$sourceAITs,
                    'sourcesOrg'=>$orgSource,
                    'targets'=>$targetAITs,
                    'targetsOrg'=>$orgTarget];
        
        return $params;
    }
    
    function parseComp($str){
        
        $orgSource2='[';
        
        if($str=="" || is_null($str))
            $orgSource2 ='(?i).*';
        else
        {
            $orgSourceArr = explode(',', $str);
            //  print_r($orgSourceArr);
            //echo '<br/>';
            foreach($orgSourceArr as $arr){
                $orgSource2= $orgSource2.'"'.$arr.'",';
            }
     //       print_r('Revised:'.$orgSource2);
       //     echo '<br/>';
            $orgSource2 = substr($orgSource2,0,strlen($orgSource2)-1);
         //   print_r('Revised2:'.$orgSource2);
           // echo '<br/>';
            $orgSource2 = $orgSource2.']';
            //print_r('Revised3:'.$orgSource2);
            //echo '<br/>';
        }
        return $orgSource2;
    }
    
    
    $app->run();
