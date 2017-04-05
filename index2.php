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
    return file_get_contents(__DIR__.'/static/index2.html');
});
    
    
$app->get('/getProducts',function(Request $request) use ($neo4j){
        $params = [];
        $limit = $request->get('limit', 50);
         $params = ['limit' => $limit];
    
          
          $query = 'match (m:Complaint) return distinct m LIMIT {limit}';
          
          $result = $neo4j->sendCypherQuery($query, $params)->getResult();
          $complaints = [];
          foreach ($result->getNodes() as $complaint){
            $complaints[] = ['product' =>$complaint->getProperty('Product')];
          }
          
          $response = new JsonResponse();
          $response->setData($complaints);
          

        return $response;
});
    
    
$app->get('/graph', function (Request $request) use ($neo4j) {
    $limit = $request->get('limit', 50);
    $searchTerm = $request->get('q');
    $term = '(?i).*'.$searchTerm.'.*';
          
    $params = ['term'=>$term,'limit' => $limit];
    $query = 'MATCH (m:Company)-[r:ComplaintExists]->(p:Complaint) WHERE m.name =~ {term} RETURN m,r,p LIMIT {limit}';
    $result = $neo4j->sendCypherQuery($query, $params)->getResult();
    $nodes = [];
    $edges = [];
    $nodesPositions = [];
          $minSize = 1;
          $maxSize = 15;

    $i = 0;
    foreach ($result->getNodes() as $node){
        $prop = ($node->getLabel() === 'Company') ? 'name' : 'Product';
          
        $nodes[] = [
            'name' => $node->getProperty($prop),
            'title' => $node->getProperty($prop),
            'label' => $node->getLabel()
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

    $response = new JsonResponse();
    $response->setData($data);

    return $response;
});

$app->get('/search', function (Request $request) use ($neo4j) {
          $searchTerm = $request->get('q');
          $term = '(?i).*'.$searchTerm.'.*';
          $query = 'MATCH (m:Company) WHERE m.name =~ {term} RETURN m';
          $params = ['term' => $term];
          
          $result = $neo4j->sendCypherQuery($query, $params)->getResult();
          $companies = [];
          foreach ($result->getNodes() as $company){
          $companies[] = ['company' => $company->getProperties()];
          }
          
          $response = new JsonResponse();
          $response->setData($companies);
          
          return $response;
});

$app->get('/company/{name}', function ($name) use ($neo4j) {
  
          $q = 'MATCH (m:Company) WHERE m.name = {name} OPTIONAL MATCH p=(m)-[r]->(a:Complaint) RETURN m,p LIMIT 5';
          $params = ['name' => $name];
          
          $result = $neo4j->sendCypherQuery($q, $params)->getResult();
          
          $company = $result->getSingleNodeByLabel('Company');
          $comp = [
          'name' => $company->getProperty('name'),
          'complaints' => []

          ];
          
          foreach ($company->getOutboundRelationships() as $rel){
          $product = $rel->getEndNode()->getProperty('Product');
          $zipCode = $rel->getEndNode()->getProperty('consumerZipCode');
          $complaintID = $rel->getEndNode()->getProperty('ComplaintID');
          $relType = explode('_', strtolower($rel->getType()));
          $isComplaint = $relType[0];
          $complaints = [
          'complaintExists' => $isComplaint,
          'complaintID' => $complaintID,
          'name' => $product,
          'zipCode'=>$zipCode
          
          ];
          $comp['complaints'][] = $complaints;
          }
          
          $response = new JsonResponse();
          $response->setData($comp);
          
          return $response;
});
    
$app->run();
    

