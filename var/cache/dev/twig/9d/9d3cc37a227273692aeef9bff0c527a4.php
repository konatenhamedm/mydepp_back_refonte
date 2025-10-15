<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* emails/template.html.twig */
class __TwigTemplate_92e6c588552566f0eeea8603194dcb8d extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/template.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/template.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html>
<head>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <style>
        * {
            box-sizing: border-box;
        }

        .menu {
            float: left;
            width: 20%;
            text-align: center;
        }

        .menu a {
            background-color: #e5e5e5;
            padding: 8px;
            margin-top: 7px;
            display: block;
            width: 100%;
            color: black;
        }

        .main {
            float: left;
            width: 60%;
            padding: 0 20px;
        }

        .right {
            background-color: #e5e5e5;
            float: left;
            width: 20%;
            padding: 15px;
            margin-top: 7px;
            text-align: center;
        }

        @media only screen and (max-width: 620px) {
            /* For mobile phones: */
            .menu, .main, .right {
                width: 100%;
            }
        }
    </style>
</head>
<body style=\"font-family:Verdana;color:#aaaaaa;\">

<div style=\"background-color:#ffff;padding:15px;text-align:center;\">
        <img src=\"https://mydepps.net/_files/logo-depps.png\" width=\"200\" height=\"200\">

</div>
<hr size=\"10\" style=\"background-color:#2AD10F;\">
<div style=\"overflow:auto\">


    <div class=\"main\">
        <p> Vous avez reçu un nouveau message de chez UFR SEG.</p>
        <p> Vous avez un examen prevu pour le ";
        // line 60
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate((isset($context["date"]) || array_key_exists("date", $context) ? $context["date"] : (function () { throw new RuntimeError('Variable "date" does not exist.', 60, $this->source); })()), "d/m/Y"), "html", null, true);
        yield " dans les matières suivantes:</p>


    <table class=\"table table-bordered table-custom\">
        <thead class=\"thead-dark\">
            <tr>
                <th width=\"70%\" class=\"p-2\">Matière</th>
                <th width=\"25%\" class=\"p-2\">Coefficient</th>
                ";
        // line 69
        yield "            </tr>
        </thead>
        <tbody  class=\"row-container\">
          ";
        // line 72
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable($context["data"]);
        foreach ($context['_seq'] as $context["_key"] => $context["data"]) {
            // line 73
            yield "              <tr class=\"row-colonne even pointer table-light\">
                <td class=\"p-2\">
                    <span class=\"label\">";
            // line 75
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["data"], "matiere", [], "any", false, false, false, 75), "html", null, true);
            yield "</span>
                    
                </td>
                <td class=\"p-2\">";
            // line 78
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, $context["data"], "coefficient", [], "any", false, false, false, 78), "html", null, true);
            yield "</td>
               ";
            // line 80
            yield "            </tr>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['data'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 82
        yield "        </tbody>
    </table>

    </div>


</div>
<br>
<div style=\"background-color:#2AD10F;text-align:center;padding:10px;margin-top:7px;\">© copyright ufr-seg</div>

</body>
</html>
";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "emails/template.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  150 => 82,  143 => 80,  139 => 78,  133 => 75,  129 => 73,  125 => 72,  120 => 69,  109 => 60,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html>
<head>
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <style>
        * {
            box-sizing: border-box;
        }

        .menu {
            float: left;
            width: 20%;
            text-align: center;
        }

        .menu a {
            background-color: #e5e5e5;
            padding: 8px;
            margin-top: 7px;
            display: block;
            width: 100%;
            color: black;
        }

        .main {
            float: left;
            width: 60%;
            padding: 0 20px;
        }

        .right {
            background-color: #e5e5e5;
            float: left;
            width: 20%;
            padding: 15px;
            margin-top: 7px;
            text-align: center;
        }

        @media only screen and (max-width: 620px) {
            /* For mobile phones: */
            .menu, .main, .right {
                width: 100%;
            }
        }
    </style>
</head>
<body style=\"font-family:Verdana;color:#aaaaaa;\">

<div style=\"background-color:#ffff;padding:15px;text-align:center;\">
        <img src=\"https://mydepps.net/_files/logo-depps.png\" width=\"200\" height=\"200\">

</div>
<hr size=\"10\" style=\"background-color:#2AD10F;\">
<div style=\"overflow:auto\">


    <div class=\"main\">
        <p> Vous avez reçu un nouveau message de chez UFR SEG.</p>
        <p> Vous avez un examen prevu pour le {{ date | date('d/m/Y') }} dans les matières suivantes:</p>


    <table class=\"table table-bordered table-custom\">
        <thead class=\"thead-dark\">
            <tr>
                <th width=\"70%\" class=\"p-2\">Matière</th>
                <th width=\"25%\" class=\"p-2\">Coefficient</th>
                {# <th></th> #}
            </tr>
        </thead>
        <tbody  class=\"row-container\">
          {% for data in data %}
              <tr class=\"row-colonne even pointer table-light\">
                <td class=\"p-2\">
                    <span class=\"label\">{{ data.matiere  }}</span>
                    
                </td>
                <td class=\"p-2\">{{ data.coefficient }}</td>
               {#  <td class=\"p-2 del-col\"><a href=\"#\" class=\"btn btn-danger btn-xs\"><span class=\"bi bi-trash\"></span></a></td> #}
            </tr>
          {% endfor %}
        </tbody>
    </table>

    </div>


</div>
<br>
<div style=\"background-color:#2AD10F;text-align:center;padding:10px;margin-top:7px;\">© copyright ufr-seg</div>

</body>
</html>
", "emails/template.html.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/template.html.twig");
    }
}
