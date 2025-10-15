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

/* emails/content_mail.html copy.twig */
class __TwigTemplate_08b04e6f9bb783eea248d87ac653a950 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/content_mail.html copy.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/content_mail.html copy.twig"));

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
<body >

<div style=\"background-color:#ffff;padding:15px;text-align:center;\">
        <img src=\"https://mydepps.net/_files/logo-depps.png\" width=\"200\" height=\"200\">

</div>
<hr size=\"10\" style=\"background-color:#2AD10F;\">
<div style=\"overflow:auto\">


    <div class=\"main\">
        <p> Voici les informations concernant votre compte utilisateur .</p>

        <p>Login ";
        // line 64
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 64, $this->source); })()), "login", [], "any", false, false, false, 64), "html", null, true);
        yield " </p>
        ";
        // line 66
        yield "        <p>Votre création de compte à été effectué avec success, veuillez cliquer sur le lien suivant pour vous connectez : <a href=\"https://mydepps.pages.dev/site/connexion\">Login</a></p>
        <p>Merci</p>

    </div>


</div>
<br>
<div style=\"background-color:#2AD10F;text-align:center;padding:10px;margin-top:7px;\"><h3 style=\"color: white;\">© copyright DEPPS</h3></div>

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
        return "emails/content_mail.html copy.twig";
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
        return array (  117 => 66,  113 => 64,  48 => 1,);
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
<body >

<div style=\"background-color:#ffff;padding:15px;text-align:center;\">
        <img src=\"https://mydepps.net/_files/logo-depps.png\" width=\"200\" height=\"200\">

</div>
<hr size=\"10\" style=\"background-color:#2AD10F;\">
<div style=\"overflow:auto\">


    <div class=\"main\">
        <p> Voici les informations concernant votre compte utilisateur .</p>

        <p>Login {{ info_user.login }} </p>
        {# <p>Password {{ info_user.password }}</p> #}
        <p>Votre création de compte à été effectué avec success, veuillez cliquer sur le lien suivant pour vous connectez : <a href=\"https://mydepps.pages.dev/site/connexion\">Login</a></p>
        <p>Merci</p>

    </div>


</div>
<br>
<div style=\"background-color:#2AD10F;text-align:center;padding:10px;margin-top:7px;\"><h3 style=\"color: white;\">© copyright DEPPS</h3></div>

</body>
</html>
", "emails/content_mail.html copy.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/content_mail.html copy.twig");
    }
}
