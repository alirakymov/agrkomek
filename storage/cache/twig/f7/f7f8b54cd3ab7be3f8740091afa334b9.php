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

/* @app/main.twig */
class __TwigTemplate_9f7d4f3e17cf1b5eacfa13139e5e1d30 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'head' => [$this, 'block_head'],
            'scripts' => [$this, 'block_scripts'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "@layout/main.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("@layout/main.twig", "@app/main.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        echo twig_escape_filter($this->env, (isset($context["title"]) || array_key_exists("title", $context) ? $context["title"] : (function () { throw new RuntimeError('Variable "title" does not exist.', 3, $this->source); })()), "html", null, true);
    }

    // line 5
    public function block_head($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 6
        echo "    <style type=\"text/css\" media=\"screen\">
        ";
        // line 7
        echo twig_get_attribute($this->env, $this->source, (isset($context["DebugBar"]) || array_key_exists("DebugBar", $context) ? $context["DebugBar"] : (function () { throw new RuntimeError('Variable "DebugBar" does not exist.', 7, $this->source); })()), "dumpCssAssets", [], "method", false, false, false, 7);
        echo "
    </style>
    <script charset=\"utf-8\">
        ";
        // line 10
        echo twig_get_attribute($this->env, $this->source, (isset($context["DebugBar"]) || array_key_exists("DebugBar", $context) ? $context["DebugBar"] : (function () { throw new RuntimeError('Variable "DebugBar" does not exist.', 10, $this->source); })()), "dumpJsAssets", [], "method", false, false, false, 10);
        echo "
    </script>
";
    }

    // line 14
    public function block_scripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 15
        echo "    <script>
        let appData = ";
        // line 16
        echo json_encode((isset($context["frontProtocol"]) || array_key_exists("frontProtocol", $context) ? $context["frontProtocol"] : (function () { throw new RuntimeError('Variable "frontProtocol" does not exist.', 16, $this->source); })()));
        echo ";
    </script>
    ";
        // line 18
        $this->displayParentBlock("scripts", $context, $blocks);
        echo "
";
    }

    public function getTemplateName()
    {
        return "@app/main.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  87 => 18,  82 => 16,  79 => 15,  75 => 14,  68 => 10,  62 => 7,  59 => 6,  55 => 5,  48 => 3,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends '@layout/main.twig' %}

{% block title %}{{ title }}{% endblock %}

{% block head %}
    <style type=\"text/css\" media=\"screen\">
        {{ DebugBar.dumpCssAssets() | raw }}
    </style>
    <script charset=\"utf-8\">
        {{ DebugBar.dumpJsAssets() | raw }}
    </script>
{% endblock %}

{% block scripts %}
    <script>
        let appData = {{ frontProtocol | json_encode() | raw }};
    </script>
    {{ parent() }}
{% endblock %}
", "@app/main.twig", "/usr/share/nginx/agro/frontapp/gateway/public/templates/desk/app/main.twig");
    }
}
