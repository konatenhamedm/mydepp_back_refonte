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

/* emails/content_validation.html.twig */
class __TwigTemplate_e6d8afa369df65dee95ad9d10cea2f24 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/content_validation.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/content_validation.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html>
\t<head>
\t\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
\t\t<style>
\t\t\t* {
\t\t\t\tbox-sizing: border-box;
\t\t\t}

\t\t\t.menu {
\t\t\t\tfloat: left;
\t\t\t\twidth: 20%;
\t\t\t\ttext-align: center;
\t\t\t}

\t\t\t.menu a {
\t\t\t\tbackground-color: #e5e5e5;
\t\t\t\tpadding: 8px;
\t\t\t\tmargin-top: 7px;
\t\t\t\tdisplay: block;
\t\t\t\twidth: 100%;
\t\t\t\tcolor: black;
\t\t\t}

\t\t\t.main {
\t\t\t\tfloat: left;
\t\t\t\twidth: 60%;
\t\t\t\tpadding: 0 20px;
\t\t\t}

\t\t\t.right {
\t\t\t\tbackground-color: #e5e5e5;
\t\t\t\tfloat: left;
\t\t\t\twidth: 20%;
\t\t\t\tpadding: 15px;
\t\t\t\tmargin-top: 7px;
\t\t\t\ttext-align: center;
\t\t\t}
\t\t\tp {
\t\t\t\tfont-size: 16px;
\t\t\t\tfont-weight: bolder !important;
\t\t\t\tcolor: #000;
\t\t\t}
\t\t\t@media only screen and(max-width: 620px) {
\t\t\t\t/* For mobile phones: */
\t\t\t\t.menu,
\t\t\t\t.main,
\t\t\t\t.right {
\t\t\t\t\twidth: 100%;
\t\t\t\t}
\t\t\t}
\t\t</style>
\t</head>
\t<body style=\"font-family:Verdana;color:#aaaaaa;\">

\t\t<div style=\"background-color:#ffff;padding:15px;text-align:center;\">
\t\t\t<img src=\"https://mydepps.net/_files/logo-depps.png\" width=\"200\" height=\"200\">
\t\t</div>
\t\t<hr size=\"10\" style=\"background-color:#2AD10F;\">
\t\t<div style=\"overflow:auto\">


\t\t\t";
        // line 63
        if ((CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 63, $this->source); })()), "etape", [], "any", false, false, false, 63) == "acceptation")) {
            // line 64
            yield "\t\t\t\t<div style=\"background-color:rgb(255, 255, 255); padding: 20px; border-radius: 10px; color: #000;\">
\t\t\t\t\t<p>
\t\t\t\t\t\t<strong>Courrier - réponse</strong>
\t\t\t\t\t</p>
\t\t\t\t\t<p>A</p>
\t\t\t\t\t<p>M/Mme ";
            // line 69
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 69, $this->source); })()), "nom", [], "any", false, false, false, 69), "html", null, true);
            yield ", </p>

\t\t\t\t\t<p>Merci d’avoir contacté la Direction des établissements privées et des professions sanitaires (DEPPS). 
\t\t\t\t\t\tNous tenons à confirmer que nous avons bien reçu votre demande et que votre dossier est en instruction. 
\t\t\t\t\t\tUne suite sera donnée à votre demande relative à l’inscription au registre des ";
            // line 73
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 73, $this->source); })()), "profession", [], "any", false, false, false, 73), "html", null, true);
            yield "</p>

\t\t\t\t\t<p>Pour toute information complémentaire, veuillez contacter la Direction des Etablissements privées 
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t                et des Professions Sanitaires directement au
\t\t\t\t\t\t<strong>07 68 15 32 21</strong>.</p>
\t\t\t\t</div>
\t\t\t";
        } elseif ((CoreExtension::getAttribute($this->env, $this->source,         // line 79
(isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 79, $this->source); })()), "etape", [], "any", false, false, false, 79) == "validation")) {
            // line 80
            yield "\t\t\t\t<div style=\"background-color: rgb(255, 255, 255); padding: 20px; border-radius: 10px; color: #000;\">
\t\t\t\t\t<p>
\t\t\t\t\t\t<strong>Courrier - réponse</strong>
\t\t\t\t\t</p>
\t\t\t\t\t<p>
\t\t\t\t\t\t<strong>Avis favorable</strong>
\t\t\t\t\t</p>
\t\t\t\t\t<p>M /Mme ";
            // line 87
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 87, $this->source); })()), "nom", [], "any", false, false, false, 87), "html", null, true);
            yield ",</p>

\t\t\t\t\t<p>Après instruction de votre dossier et la tenue de la commission de recensement, nous avons le plaisir de vous annoncer que votre demande a été acceptée.</p>

\t\t\t\t\t<p>Vous êtes inscrit au registre des ";
            // line 91
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 91, $this->source); })()), "profession", [], "any", false, false, false, 91), "html", null, true);
            yield "<em>(Profession)</em> habilité(e) à exercer au titre de l’année ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 91, $this->source); })()), "annee", [], "any", false, false, false, 91), "html", null, true);
            yield " <em>(Année)</em>.</p>

\t\t\t\t\t<p>À cet effet vous êtes priés de vous rendre à la Direction des Etablissements Privés et des Professions Sanitaires (DEPPS) pour le retrait de votre attestation d’inscription au Registre des ";
            // line 93
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 93, $this->source); })()), "profession", [], "any", false, false, false, 93), "html", null, true);
            yield " et de la Carte Professionnelle au titre de l’ année ";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 93, $this->source); })()), "annee", [], "any", false, false, false, 93), "html", null, true);
            yield "</p>
\t\t\t\t\t<p>Pour toute information complémentaire, veuillez contacter la Direction des Etablissements Privés et des Professions Sanitaires au  07 68 15 32 21</p>
\t\t\t\t</div>

\t\t\t";
        } else {
            // line 98
            yield "\t\t\t\t<div class=\"main\">
\t\t\t\t\t";
            // line 100
            yield "\t\t\t\t\t";
            if ((($tmp =  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 100, $this->source); })()), "message", [], "any", false, false, false, 100))) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 101
                yield "
\t\t\t\t\t\t<p>";
                // line 102
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 102, $this->source); })()), "message", [], "any", false, false, false, 102), "html", null, true);
                yield "
\t\t\t\t\t\t</p>
\t\t\t\t\t";
            } else {
                // line 105
                yield "
\t\t\t\t\t\t<p>Votre dossier vient passer l'étape
\t\t\t\t\t\t\t";
                // line 107
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 107, $this->source); })()), "etape", [], "any", false, false, false, 107), "html", null, true);
                yield "
\t\t\t\t\t\t\ttraité par
\t\t\t\t\t\t\t";
                // line 109
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 109, $this->source); })()), "user", [], "any", false, false, false, 109), "html", null, true);
                yield "
\t\t\t\t\t\t</p>
\t\t\t\t\t";
            }
            // line 112
            yield "
\t\t\t\t</div>

\t\t\t";
        }
        // line 116
        yield "

\t\t</div>
\t\t<br>
\t\t<div style=\"background-color:#4b9bd8;text-align:center;padding:10px;margin-top:7px;\">
\t\t\t<h3 style=\"color: white;\">© copyright DEPPS</h3>
\t\t</div>

\t</body>
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
        return "emails/content_validation.html.twig";
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
        return array (  208 => 116,  202 => 112,  196 => 109,  191 => 107,  187 => 105,  181 => 102,  178 => 101,  175 => 100,  172 => 98,  162 => 93,  155 => 91,  148 => 87,  139 => 80,  137 => 79,  128 => 73,  121 => 69,  114 => 64,  112 => 63,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html>
\t<head>
\t\t<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
\t\t<style>
\t\t\t* {
\t\t\t\tbox-sizing: border-box;
\t\t\t}

\t\t\t.menu {
\t\t\t\tfloat: left;
\t\t\t\twidth: 20%;
\t\t\t\ttext-align: center;
\t\t\t}

\t\t\t.menu a {
\t\t\t\tbackground-color: #e5e5e5;
\t\t\t\tpadding: 8px;
\t\t\t\tmargin-top: 7px;
\t\t\t\tdisplay: block;
\t\t\t\twidth: 100%;
\t\t\t\tcolor: black;
\t\t\t}

\t\t\t.main {
\t\t\t\tfloat: left;
\t\t\t\twidth: 60%;
\t\t\t\tpadding: 0 20px;
\t\t\t}

\t\t\t.right {
\t\t\t\tbackground-color: #e5e5e5;
\t\t\t\tfloat: left;
\t\t\t\twidth: 20%;
\t\t\t\tpadding: 15px;
\t\t\t\tmargin-top: 7px;
\t\t\t\ttext-align: center;
\t\t\t}
\t\t\tp {
\t\t\t\tfont-size: 16px;
\t\t\t\tfont-weight: bolder !important;
\t\t\t\tcolor: #000;
\t\t\t}
\t\t\t@media only screen and(max-width: 620px) {
\t\t\t\t/* For mobile phones: */
\t\t\t\t.menu,
\t\t\t\t.main,
\t\t\t\t.right {
\t\t\t\t\twidth: 100%;
\t\t\t\t}
\t\t\t}
\t\t</style>
\t</head>
\t<body style=\"font-family:Verdana;color:#aaaaaa;\">

\t\t<div style=\"background-color:#ffff;padding:15px;text-align:center;\">
\t\t\t<img src=\"https://mydepps.net/_files/logo-depps.png\" width=\"200\" height=\"200\">
\t\t</div>
\t\t<hr size=\"10\" style=\"background-color:#2AD10F;\">
\t\t<div style=\"overflow:auto\">


\t\t\t{% if info_user.etape == \"acceptation\" %}
\t\t\t\t<div style=\"background-color:rgb(255, 255, 255); padding: 20px; border-radius: 10px; color: #000;\">
\t\t\t\t\t<p>
\t\t\t\t\t\t<strong>Courrier - réponse</strong>
\t\t\t\t\t</p>
\t\t\t\t\t<p>A</p>
\t\t\t\t\t<p>M/Mme {{ info_user.nom }}, </p>

\t\t\t\t\t<p>Merci d’avoir contacté la Direction des établissements privées et des professions sanitaires (DEPPS). 
\t\t\t\t\t\tNous tenons à confirmer que nous avons bien reçu votre demande et que votre dossier est en instruction. 
\t\t\t\t\t\tUne suite sera donnée à votre demande relative à l’inscription au registre des {{ info_user.profession }}</p>

\t\t\t\t\t<p>Pour toute information complémentaire, veuillez contacter la Direction des Etablissements privées 
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t                et des Professions Sanitaires directement au
\t\t\t\t\t\t<strong>07 68 15 32 21</strong>.</p>
\t\t\t\t</div>
\t\t\t{% elseif info_user.etape == \"validation\" %}
\t\t\t\t<div style=\"background-color: rgb(255, 255, 255); padding: 20px; border-radius: 10px; color: #000;\">
\t\t\t\t\t<p>
\t\t\t\t\t\t<strong>Courrier - réponse</strong>
\t\t\t\t\t</p>
\t\t\t\t\t<p>
\t\t\t\t\t\t<strong>Avis favorable</strong>
\t\t\t\t\t</p>
\t\t\t\t\t<p>M /Mme {{ info_user.nom }},</p>

\t\t\t\t\t<p>Après instruction de votre dossier et la tenue de la commission de recensement, nous avons le plaisir de vous annoncer que votre demande a été acceptée.</p>

\t\t\t\t\t<p>Vous êtes inscrit au registre des {{ info_user.profession }}<em>(Profession)</em> habilité(e) à exercer au titre de l’année {{ info_user.annee }} <em>(Année)</em>.</p>

\t\t\t\t\t<p>À cet effet vous êtes priés de vous rendre à la Direction des Etablissements Privés et des Professions Sanitaires (DEPPS) pour le retrait de votre attestation d’inscription au Registre des {{ info_user.profession }} et de la Carte Professionnelle au titre de l’ année {{ info_user.annee }}</p>
\t\t\t\t\t<p>Pour toute information complémentaire, veuillez contacter la Direction des Etablissements Privés et des Professions Sanitaires au  07 68 15 32 21</p>
\t\t\t\t</div>

\t\t\t{% else %}
\t\t\t\t<div class=\"main\">
\t\t\t\t\t{#if message is not empty #}
\t\t\t\t\t{% if info_user.message is not empty %}

\t\t\t\t\t\t<p>{{ info_user.message }}
\t\t\t\t\t\t</p>
\t\t\t\t\t{% else %}

\t\t\t\t\t\t<p>Votre dossier vient passer l'étape
\t\t\t\t\t\t\t{{ info_user.etape }}
\t\t\t\t\t\t\ttraité par
\t\t\t\t\t\t\t{{ info_user.user }}
\t\t\t\t\t\t</p>
\t\t\t\t\t{% endif %}

\t\t\t\t</div>

\t\t\t{% endif %}


\t\t</div>
\t\t<br>
\t\t<div style=\"background-color:#4b9bd8;text-align:center;padding:10px;margin-top:7px;\">
\t\t\t<h3 style=\"color: white;\">© copyright DEPPS</h3>
\t\t</div>

\t</body>
</html>
", "emails/content_validation.html.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/content_validation.html.twig");
    }
}
