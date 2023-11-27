<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* @layout/main.twig */
class __TwigTemplate_62b93a23de0ebf0afa698a0e44e6f209 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'head' => [$this, 'block_head'],
            'main' => [$this, 'block_main'],
            'scripts' => [$this, 'block_scripts'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"utf-8\"/>

        <title>";
        // line 6
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <meta name=\"description\" content=\"Qore Framework\"/>
        <meta name=\"author\" content=\"pixelcave\"/>
        <meta name=\"csrf-token\" content=\"";
        // line 9
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["csrf"]) || array_key_exists("csrf", $context) ? $context["csrf"] : (function () { throw new RuntimeError('Variable "csrf" does not exist.', 9, $this->source); })()), "generateToken", [], "method", false, false, false, 9), "html", null, true);
        echo "\"/>
        <meta name=\"robots\" content=\"noindex, nofollow\"/>
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0,user-scalable=0\"/>

        <!-- Fonts and OneUI framework -->
        <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
        <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
        <link href=\"https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&display=swap\" rel=\"stylesheet\">

        ";
        // line 22
        echo "
            
                <link rel=\"stylesheet\" href=\"/static-gateway/assets/css/app.bundle.1760ebc069a75579de20.css\" >
            
        ";
        echo "

        ";
        // line 24
        $this->displayBlock('head', $context, $blocks);
        // line 26
        echo "    </head>
    <body>
        ";
        // line 28
        $this->displayBlock('main', $context, $blocks);
        // line 39
        echo "        ";
        $this->displayBlock('scripts', $context, $blocks);
        // line 96
        echo "        ";
        echo twig_get_attribute($this->env, $this->source, (isset($context["DebugBar"]) || array_key_exists("DebugBar", $context) ? $context["DebugBar"] : (function () { throw new RuntimeError('Variable "DebugBar" does not exist.', 96, $this->source); })()), "render", [], "method", false, false, false, 96);
        echo "
    </body>
</html>
";
    }

    // line 6
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo "Qore.CRM";
    }

    // line 24
    public function block_head($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 25
        echo "        ";
    }

    // line 28
    public function block_main($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 29
        echo "            <div id=\"qore-app\">
                <component v-for=\"component in components\"
                    :is=\"component.type\"
                    :key=\"component.id\"
                    :options=\"component.data\"
                    ref=\"children\"
                    @cdestroy=\"cdestroy()\"
                />
            </div>
        ";
    }

    // line 39
    public function block_scripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 40
        echo "            
                <script type=\"module\" src=\"/static-gateway/assets/js/_ckeditor.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_es.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/core_js.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_popperjs.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/axios.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_juggle.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/flatpickr.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_vue.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/bootstrap.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_ckpack.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/vue3_json_viewer.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/vue_draggable_next.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/v_mask.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/simplebar.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/regenerator_runtime.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/nouislider.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_throttle.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_memoize.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_debounce.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/dropzone.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/cropperjs.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_elricco.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_editorjs.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/qore.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/app.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_stomp.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/app.worker.bundle.js\" async></script>
            
        ";
    }

    public function getTemplateName()
    {
        return "@layout/main.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  131 => 40,  127 => 39,  114 => 29,  110 => 28,  106 => 25,  102 => 24,  95 => 6,  86 => 96,  83 => 39,  81 => 28,  77 => 26,  75 => 24,  66 => 22,  54 => 9,  48 => 6,  41 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"utf-8\"/>

        <title>{% block title %}Qore.CRM{% endblock %}</title>
        <meta name=\"description\" content=\"Qore Framework\"/>
        <meta name=\"author\" content=\"pixelcave\"/>
        <meta name=\"csrf-token\" content=\"{{ csrf.generateToken() }}\"/>
        <meta name=\"robots\" content=\"noindex, nofollow\"/>
        <meta name=\"viewport\" content=\"width=device-width,initial-scale=1.0,user-scalable=0\"/>

        <!-- Fonts and OneUI framework -->
        <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
        <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
        <link href=\"https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&display=swap\" rel=\"stylesheet\">

        {% verbatim %}
            
                <link rel=\"stylesheet\" href=\"/static-gateway/assets/css/app.bundle.1760ebc069a75579de20.css\" >
            
        {% endverbatim %}

        {% block head %}
        {% endblock %}
    </head>
    <body>
        {% block main %}
            <div id=\"qore-app\">
                <component v-for=\"component in components\"
                    :is=\"component.type\"
                    :key=\"component.id\"
                    :options=\"component.data\"
                    ref=\"children\"
                    @cdestroy=\"cdestroy()\"
                />
            </div>
        {% endblock %}
        {% block scripts %}
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_ckeditor.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_es.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/core_js.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_popperjs.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/axios.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_juggle.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/flatpickr.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_vue.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/bootstrap.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_ckpack.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/vue3_json_viewer.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/vue_draggable_next.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/v_mask.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/simplebar.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/regenerator_runtime.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/nouislider.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_throttle.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_memoize.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/lodash_debounce.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/dropzone.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/cropperjs.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_elricco.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_editorjs.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/qore.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/app.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/_stomp.bundle.js\" async></script>
            
                <script type=\"module\" src=\"/static-gateway/assets/js/app.worker.bundle.js\" async></script>
            
        {% endblock %}
        {{ DebugBar.render() | raw }}
    </body>
</html>
", "@layout/main.twig", "/usr/share/nginx/agro/frontapp/gateway/public/templates/desk/layout/main.twig");
    }
}
