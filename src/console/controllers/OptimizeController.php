<?php
/**
 * Image Optimize plugin for Craft CMS 3.x
 *
 * Automatically optimize images after they've been transformed
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\imageoptimize\console\controllers;

use nystudio107\imageoptimize\ImageOptimize;

use Craft;
use craft\utilities\ClearCaches;

use yii\console\Controller;
use yii\helpers\Console;

/**
 * Optimize Command
 *
 * @author    nystudio107
 * @package   ImageOptimize
 * @since     1.2.0
 */
class OptimizeController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Create all of the OptimizedImages Field variants by creating all of the responsive image variant transforms
     */
    public function actionCreate()
    {
        echo "Creating optimized image variants".PHP_EOL;

        // Re-save all of the optimized image variants in all volumes
        ImageOptimize::$plugin->optimizedImages->resaveAllVolumesAssets();
        Craft::$app->getQueue()->run();
    }

    /**
     * Clear the Asset transform index cache tables, to force the re-creation of transformed images
     */
    public function actionClear()
    {
        foreach (ClearCaches::cacheOptions() as $cacheOption) {
            if ($cacheOption['key'] !== 'transform-indexes') {
                continue;
            }

            $action = $cacheOption['action'];

            if (is_string($action)) {
                try {
                    FileHelper::clearDirectory($action);
                } catch (\Throwable $e) {
                    Craft::warning("Could not clear the directory {$action}: ".$e->getMessage(), __METHOD__);
                }
            } elseif (isset($cacheOption['params'])) {
                call_user_func_array($action, $cacheOption['params']);
            } else {
                $action();
            }
        }
    }
}