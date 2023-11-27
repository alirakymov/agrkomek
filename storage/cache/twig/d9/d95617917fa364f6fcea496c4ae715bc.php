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
        echo "<!doctype html><html><head><meta charset=\"utf-8\"/><title>";
        $this->displayBlock('title', $context, $blocks);
        echo "</title><meta name=\"description\" content=\"Qore Framework\"/><meta name=\"author\" content=\"pixelcave\"/><meta name=\"csrf-token\" content=\"";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["csrf"]) || array_key_exists("csrf", $context) ? $context["csrf"] : (function () { throw new RuntimeError('Variable "csrf" does not exist.', 1, $this->source); })()), "generateToken", [], "method", false, false, false, 1), "html", null, true);
        echo "\"/><meta name=\"robots\" content=\"noindex, nofollow\"/><meta name=\"viewport\" content=\"width=device-width,initial-scale=1,user-scalable=0\"/><link rel=\"preconnect\" href=\"https://fonts.googleapis.com\"><link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin><link href=\"https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&display=swap\" rel=\"stylesheet\">";
        echo "<link rel=\"stylesheet\" href=\"/static-gateway/assets/css/app.bundle.8fb04b1fdeb86381ee01.css\">";
        echo " ";
        $this->displayBlock('head', $context, $blocks);
        echo "</head><body>";
        $this->displayBlock('main', $context, $blocks);
        echo " ";
        $this->displayBlock('scripts', $context, $blocks);
        echo " ";
        echo twig_get_attribute($this->env, $this->source, (isset($context["DebugBar"]) || array_key_exists("DebugBar", $context) ? $context["DebugBar"] : (function () { throw new RuntimeError('Variable "DebugBar" does not exist.', 1, $this->source); })()), "render", [], "method", false, false, false, 1);
        echo "</body></html>";
    }

    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo "Qore.CRM";
    }

    public function block_head($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo " ";
    }

    public function block_main($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo "<div id=\"qore-app\"><component v-for=\"component in components\" :is=\"component.type\" :key=\"component.id\" :options=\"component.data\" ref=\"children\" @cdestroy=\"cdestroy()\"/></div>";
    }

    public function block_scripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo "<script type=\"module\" src=\"/static-gateway/assets/js/_ckeditor.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/lodash_es.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/core_js.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_popperjs.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/axios.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_juggle.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/flatpickr.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_vue.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/bootstrap.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_ckpack.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/vue3_json_viewer.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/vue_draggable_next.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/simplebar.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/regenerator_runtime.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/nouislider.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/dropzone.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/cropperjs.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_elricco.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/qore.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/app.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/app.worker.8fb04b1fdeb86381ee01.js\" async></script>";
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
        return array (  41 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!doctype html><html><head><meta charset=\"utf-8\"/><title>{% block title %}Qore.CRM{% endblock %}</title><meta name=\"description\" content=\"Qore Framework\"/><meta name=\"author\" content=\"pixelcave\"/><meta name=\"csrf-token\" content=\"{{ csrf.generateToken() }}\"/><meta name=\"robots\" content=\"noindex, nofollow\"/><meta name=\"viewport\" content=\"width=device-width,initial-scale=1,user-scalable=0\"/><link rel=\"preconnect\" href=\"https://fonts.googleapis.com\"><link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin><link href=\"https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500;1,600&display=swap\" rel=\"stylesheet\">{% verbatim %}<link rel=\"stylesheet\" href=\"/static-gateway/assets/css/app.bundle.8fb04b1fdeb86381ee01.css\">{% endverbatim %} {% block head %} {% endblock %}</head><body>{% block main %}<div id=\"qore-app\"><component v-for=\"component in components\" :is=\"component.type\" :key=\"component.id\" :options=\"component.data\" ref=\"children\" @cdestroy=\"cdestroy()\"/></div>{% endblock %} {% block scripts %}<script type=\"module\" src=\"/static-gateway/assets/js/_ckeditor.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/lodash_es.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/core_js.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_popperjs.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/axios.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_juggle.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/flatpickr.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_vue.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/bootstrap.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_ckpack.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/vue3_json_viewer.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/vue_draggable_next.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/simplebar.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/regenerator_runtime.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/nouislider.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/dropzone.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/cropperjs.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/_elricco.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/qore.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/app.8fb04b1fdeb86381ee01.js\" async></script><script type=\"module\" src=\"/static-gateway/assets/js/app.worker.8fb04b1fdeb86381ee01.js\" async></script>{% endblock %} {{ DebugBar.render() | raw }}</body></html>", "@layout/main.twig", "/usr/share/nginx/agro/frontapp/gateway/public/templates/desk/layout/main.twig");
    }
}
