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

/* emails/password_reset.html.twig */
class __TwigTemplate_3369f32f868cb8d02fa7305416feb20b extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/password_reset.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/password_reset.html.twig"));

        // line 1
        yield "


<!DOCTYPE html>
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
       ";
        // line 61
        yield "
       <p>Bonjour  ";
        // line 62
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 62, $this->source); })()), "email", [], "any", false, false, false, 62), "html", null, true);
        yield "</p>
        <p>Pour votre demande de réinitialisation de mot de passe, veuillez cliquer sur le lien suivant : <a href=\"";
        // line 63
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["url"]) || array_key_exists("url", $context) ? $context["url"] : (function () { throw new RuntimeError('Variable "url" does not exist.', 63, $this->source); })()), "html", null, true);
        yield "\">";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["url"]) || array_key_exists("url", $context) ? $context["url"] : (function () { throw new RuntimeError('Variable "url" does not exist.', 63, $this->source); })()), "html", null, true);
        yield "</a></p>
        <p>Merci</p>

    </div>


</div>
<br>
<div style=\"background-color:#4b9bd8;text-align:center;padding:10px;margin-top:7px;\"><h3 style=\"color: white;\">© copyright DEPPS</h3></div>

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
        return "emails/password_reset.html.twig";
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
        return array (  116 => 63,  112 => 62,  109 => 61,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("


<!DOCTYPE html>
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
       {#  <p> Bonjour {{ user.username }} {{ user.email }} ,voici les informations concernant votre compte utilisateur .</p> #}

       <p>Bonjour  {{ user.email }}</p>
        <p>Pour votre demande de réinitialisation de mot de passe, veuillez cliquer sur le lien suivant : <a href=\"{{ url }}\">{{ url }}</a></p>
        <p>Merci</p>

    </div>


</div>
<br>
<div style=\"background-color:#4b9bd8;text-align:center;padding:10px;margin-top:7px;\"><h3 style=\"color: white;\">© copyright DEPPS</h3></div>

</body>
</html>
", "emails/password_reset.html.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/password_reset.html.twig");
    }
}
