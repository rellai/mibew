<?php
/*
 * Copyright 2005-2013 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Mibew\Style;

// Import namespaces and classes of the core
use Mibew\Settings;
use Mibew\Handlebars\HelpersSet;

/**
 * Represents a style for operator pages
 */
class PageStyle extends AbstractStyle implements StyleInterface
{
    /**
     * Template engine for chat templates.
     *
     * @var \Handlebars\Handlebars
     */
    protected $templateEngine;

    /**
     * Object constructor
     *
     * @param string $style_name Name of the style
     */
    public function __construct($style_name)
    {
        parent::__construct($style_name);

        $templates_loader = new \Handlebars\Loader\FilesystemLoader(
            MIBEW_FS_ROOT . '/' . $this->filesPath() . '/templates_src/server_side/'
        );

        $this->templateEngine = new \Handlebars\Handlebars(array(
            'loader' => $templates_loader,
            'partials_loader' => $templates_loader,
            'helpers' => new \Handlebars\Helpers(HelpersSet::getHelpers())
        ));

        // Use custom function to escape strings
        $this->templateEngine->setEscape('safe_htmlspecialchars');
        $this->templateEngine->setEscapeArgs(array());
    }

    /**
     * Builds base path for style files. This path is relative Mibew root and
     * does not contain neither leading nor trailing slash.
     *
     * @return string Base path for style files
     */
    public function filesPath()
    {
        return 'styles/pages/' . $this->name();
    }

    /**
     * Renders template file to HTML and send it to the output
     *
     * @param string $template_name Name of the template file with neither path
     *   nor extension.
     * @param array $data Associative array of values that should be used for
     *   substitutions in a template.
     */
    public function render($template_name, $data = array())
    {
        // Prepare to output html
        start_html_output();

        // $page variable is used in included views files, so we need to create
        // it as an alias of $data argument.
        $page = $data;

        // Pass additional variables to template
        $page['mibewRoot'] = MIBEW_WEB_ROOT;
        $page['mibewVersion'] = MIBEW_VERSION;
        $page['currentLocale'] = CURRENT_LOCALE;
        $page['rtl'] = (getlocal("localedirection") == 'rtl');
        $page['stylePath'] = MIBEW_WEB_ROOT . '/' . $this->filesPath();

        echo($this->templateEngine->render($template_name, $page));
    }

    /**
     * Returns name of the style which shoud be used for the current request.
     *
     * Result of the method can depends on user role, requested page or any
     * other criteria.
     *
     * @return string Name of a style
     */
    public static function currentStyle()
    {
        // Just use the default style
        return self::defaultStyle();
    }

    /**
     * Returns name of the style which is used in the system by default.
     *
     * @return string Name of a style
     */
    public static function defaultStyle()
    {
        // Load value from system settings
        return Settings::get('page_style');
    }

    /**
     * Sets style which is used in the system by default
     *
     * @param string $style_name Name of a style
     */
    public static function setDefaultStyle($style_name)
    {
        Settings::set('page_style', $style_name);
        Settings::update();
    }

    /**
     * Returns an array which contains names of available styles.
     *
     * @param array List of styles names
     */
    public static function availableStyles()
    {
        $styles_root = MIBEW_FS_ROOT . '/styles/pages';

        return self::getStyleList($styles_root);
    }

    /**
     * Returns array of default configurations for concrete style object. This
     * method uses "Template method" design pattern.
     *
     * @return array Default configurations of the style
     */
    protected function defaultConfigurations()
    {
        return array(
            'chat' => array(
                'window_params' => ''
            ),
            'mail' => array(
                'window_params' => ''
            ),
            'screenshots' => array(),
        );
    }
}
