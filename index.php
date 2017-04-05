<?php
    use Silex\Application;
    use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\Yaml\Yaml;
    use Neoxygen\NeoClient\ClientBuilder;
    
    require __DIR__.'/vendor/autoload.php';
    
    $app = new Application();
    
    if (false !== getenv('GRAPHSTORY_URL')) {
        $cnx = parse_url(getenv('GRAPHSTORY_URL'));
    } else {
        $config = Yaml::parse(file_get_contents(__DIR__.'/config/config.yml'));
        $cnx = parse_url($config['neo4j_url']);
    }
    
    $neo4j = ClientBuilder::create()
    ->addConnection('default', $cnx['scheme'], $cnx['host'], $cnx['port'], true, $cnx['user'], $cnx['pass'])
    ->setAutoFormatResponse(true)
    ->setDefaultTimeout(20)
    ->build();
    
    $app->get('/', function () {
              return file_get_contents(__DIR__.'/static/index4.html');
              });
    
    $app->get('/graph', function (Request $request) use ($neo4j) {
              $sourceAITs="";
              $targetAITs="";
              $limit = $request->get('limit', 50);
              $sourceAITs = $request->get('sourceAITs');
              $targetAITs = $request->get('targetAITs');
              $sourceAITs = '(?i).*'.$sourceAITs.'.*';
              $targetAITs = '(?i).*'.$targetAITs.'.*';
              
              $params = [];
              
              $params = ['sources'=>$sourceAITs,
              'targets'=>$targetAITs,
              'limit' => $limit];
              $query = 'MATCH (m:System)-[r:DataFlow*..]-(p:System) where m.name =~{sources} and p.name=~{targets} RETURN m,r,p LIMIT {limit}';
              //   print_r($query);
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
              // $sourceAITs="";
              //$targetAITs="";
              $sourceAITs = $request->get('sourceAITs');
              $targetAITs = $request->get('targetAITs');
              $sourceAITs = '(?i).*'.$sourceAITs.'.*';
              $targetAITs = '(?i).*'.$targetAITs.'.*';
              $orgOptionsSource = $request->get('sourceBU');
              $adsOptionsSource = $request->get('sourceADS');
              $orgOptionsTarget = $request->get('targetBU');
              $adsOptionsTarget = $request->get('targetADS');
              
              //print_r($term);
              $params = ['sources'=>$sourceAITs,
              'targets'=>$targetAITs];
              $query = 'MATCH (m:System)-[r:DataFlow]-(p:System) where m.name =~ {sources} and p.name=~ {targets} RETURN m,p';
              //and m.Org= $sourceOrg and p.Org = $targetOrg \
              //and m.isADS= $sourceADS and p.isADS = $targetADS \
              
              //  print($query);
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
    
    
    
    $app->run();
