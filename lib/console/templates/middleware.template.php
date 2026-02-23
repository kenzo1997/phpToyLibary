<?phpS

namespace {{namespace}};

use lib\controller\Request;
use lib\controller\Response;

/**
 * {{name}} Middleware
 *
 * @package {{namespace}}
 * @created {{CREATION_DATE}}
 */
class {{name}} {
    
    public function __construct() {
        // Inject services here if needed
    }

    public function run(Request $request, Response $response, array $allowedRoles=[]): void {
        // Middleware logic here
        // Example:
        // if (!$request->getParam('authenticated')) {
        //     $response->redirect('/');
        //     return;
        // }

        echo "{{name}} middleware executed.\n";
    }
}
