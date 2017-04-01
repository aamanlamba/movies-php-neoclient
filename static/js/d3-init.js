
function init() {
    var neo4jd3 = new Neo4jd3('#neo4jd3', {
                            icons: {
                              'Company': 'github',
                              'Complaint': 'paw',
                              
                              'Github': 'github',
                              //                        'icons': 'font-awesome',
                              'Ip': 'map-marker',
                              'Issues': 'exclamation-circle',
                              'Language': 'language',
                              'Options': 'sliders',
                              'Password': 'lock',
                              'Phone': 'phone',
                              'Project': 'folder-open',
                              'SecurityChallengeAnswer': 'commenting',
                              'User': 'user',
                              'zoomFit': 'arrows-alt',
                              'zoomIn': 'search-plus',
                              'zoomOut': 'search-minus'
                              },
                              images: {
                              'Address': '/static/img/twemoji/1f3e0.svg',
                              //                        'Api': '/static/img/twemoji/1f527.svg',
                              'BirthDate': '/static/img/twemoji/1f382.svg',
                              'Complaint': '/static/img/twemoji/1f36a.svg',
                              'CreditCard': '/static/img/twemoji/1f4b3.svg',
                              'Device': '/static/img/twemoji/1f4bb.svg',
                              'Email': '/static/img/twemoji/2709.svg',
                              'Git': '/static/img/twemoji/1f5c3.svg',
                              'Company': '/static/img/twemoji/1f5c4.svg',
                              'icons': '/static/img/twemoji/1f38f.svg',
                              'Ip': '/static/img/twemoji/1f4cd.svg',
                              'Issues': '/static/img/twemoji/1f4a9.svg',
                              'Language': '/static/img/twemoji/1f1f1-1f1f7.svg',
                              'Options': '/static/img/twemoji/2699.svg',
                              'Password': '/static/img/twemoji/1f511.svg',
                              //                        'Phone': '/static/img/twemoji/1f4de.svg',
                              'Project': '/static/img/twemoji/2198.svg',
                              'Project|name|neo4jd3': '/static/img/twemoji/2196.svg',
                              //                        'SecurityChallengeAnswer': '/static/img/twemoji/1f4ac.svg',
                              'User': '/static/img/twemoji/1f600.svg'
                              //                        'zoomFit': '/static/img/twemoji/2194.svg',
                              //                        'zoomIn': '/static/img/twemoji/1f50d.svg',
                              //                        'zoomOut': '/static/img/twemoji/1f50e.svg'
                              },
                              minCollision: 60,
                              neo4jDataUrl: '/static/json/result.json',
                              nodeRadius: 25,
                              onNodeDoubleClick: function(node) {
                              switch(node.labels[0]) {
                              default:
                              var maxNodes = 5,
                              data = neo4jd3.randomD3Data(node, maxNodes);
                              neo4jd3.updateWithD3Data(data);
                              break;
                              }
                              },
                              onRelationshipDoubleClick: function(relationship) {
                              console.log('double click on relationship: ' + JSON.stringify(relationship));
                              },
                              zoomFit: true
                              });
}

window.onload = init;
