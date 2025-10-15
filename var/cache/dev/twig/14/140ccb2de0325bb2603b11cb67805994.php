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

/* emails/new.twig */
class __TwigTemplate_f107961210d411299f459a042b0fa0a3 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/new.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/new.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>Vos identifiants de connexion</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 30px 15px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      overflow: hidden;
      border: 1px solid #e6e9ec;
      animation: fadeIn 0.7s ease-out;
    }

    .header {
      background: #f0f9fa;
      padding: 20px;
      text-align: center;
      border-bottom: 2px solid #53B0B7;
    }

    .header img {
      max-width: 140px;
      height: auto;
      margin-bottom: 8px;
    }

    .header h1 {
      margin: 5px 0 0;
      font-size: 20px;
      color: #53B0B7;
    }

    .content {
      padding: 30px 25px;
      font-size: 15px;
      color: #444;
      line-height: 1.6;
    }

    .content h2 {
      font-size: 18px;
      margin-bottom: 15px;
      color: #222;
      text-align: center;
    }

    .highlight {
      background: #eaf7f8;
      border: 1px solid #53B0B7;
      border-radius: 8px;
      text-align: center;
      padding: 15px;
      margin: 20px 0;
      font-size: 18px;
      font-weight: bold;
      color: #53B0B7;
    }

    .credentials {
      background: #f9fafa;
      border: 1px solid #e3e6ea;
      border-radius: 8px;
      padding: 15px 20px;
      margin: 20px 0;
      font-size: 15px;
    }

    .credentials p {
      margin: 8px 0;
    }

    .credentials span {
      font-weight: bold;
      color: #222;
    }

    .btn {
      display: inline-block;
      background: #53B0B7;
      color: #fff;
      padding: 10px 25px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #3f959b;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #888;
      padding: 15px;
      border-top: 1px solid #e6e9ec;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class=\"container\">
    <!-- HEADER -->
    <div class=\"header\">
      <img src=\"https://mydepps.net/_files/logo-depps.png\" alt=\"Logo DEPPS\">
      <h1>👋 Bienvenue sur DEPPS</h1>
    </div>

    <!-- CONTENT -->
    <div class=\"content\">
      <h2>Votre compte vient d’être créé par un administrateur</h2>
      <p>Bonjour <b>";
        // line 129
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 129, $this->source); })()), "prenom", [], "any", false, false, false, 129), "html", null, true);
        yield " ";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 129, $this->source); })()), "nom", [], "any", false, false, false, 129), "html", null, true);
        yield "</b>,</p>
      <p>
        Un compte vient d’être créé pour vous sur la plateforme <b>MyDEPPS</b>.  
        Vous pouvez dès maintenant vous connecter en utilisant les informations ci-dessous :
      </p>

      <div class=\"credentials\">
        <p><span>Adresse e-mail :</span> ";
        // line 136
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["user"]) || array_key_exists("user", $context) ? $context["user"] : (function () { throw new RuntimeError('Variable "user" does not exist.', 136, $this->source); })()), "email", [], "any", false, false, false, 136), "html", null, true);
        yield "</p>
        <p><span>Mot de passe temporaire :</span> ";
        // line 137
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["password"]) || array_key_exists("password", $context) ? $context["password"] : (function () { throw new RuntimeError('Variable "password" does not exist.', 137, $this->source); })()), "html", null, true);
        yield "</p>
      </div>

      <p style=\"text-align: center; margin-top: 20px;\">
        <a href=\"";
        // line 141
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["login_url"]) || array_key_exists("login_url", $context) ? $context["login_url"] : (function () { throw new RuntimeError('Variable "login_url" does not exist.', 141, $this->source); })()), "html", null, true);
        yield "\" class=\"btn\">Se connecter</a>
      </p>

      <p style=\"margin-top: 25px; font-size: 14px; color: #555;\">
        Pour des raisons de sécurité, nous vous recommandons de modifier votre mot de passe après votre première connexion.
      </p>
    </div>

    <!-- FOOTER -->
    <div class=\"footer\">
      Email automatique — © DEPPS ";
        // line 151
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->extensions['Twig\Extension\CoreExtension']->formatDate("now", "Y"), "html", null, true);
        yield "
    </div>
  </div>
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
        return "emails/new.twig";
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
        return array (  214 => 151,  201 => 141,  194 => 137,  190 => 136,  178 => 129,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>Vos identifiants de connexion</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 30px 15px;
    }

    .container {
      max-width: 600px;
      margin: auto;
      background: #ffffff;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      overflow: hidden;
      border: 1px solid #e6e9ec;
      animation: fadeIn 0.7s ease-out;
    }

    .header {
      background: #f0f9fa;
      padding: 20px;
      text-align: center;
      border-bottom: 2px solid #53B0B7;
    }

    .header img {
      max-width: 140px;
      height: auto;
      margin-bottom: 8px;
    }

    .header h1 {
      margin: 5px 0 0;
      font-size: 20px;
      color: #53B0B7;
    }

    .content {
      padding: 30px 25px;
      font-size: 15px;
      color: #444;
      line-height: 1.6;
    }

    .content h2 {
      font-size: 18px;
      margin-bottom: 15px;
      color: #222;
      text-align: center;
    }

    .highlight {
      background: #eaf7f8;
      border: 1px solid #53B0B7;
      border-radius: 8px;
      text-align: center;
      padding: 15px;
      margin: 20px 0;
      font-size: 18px;
      font-weight: bold;
      color: #53B0B7;
    }

    .credentials {
      background: #f9fafa;
      border: 1px solid #e3e6ea;
      border-radius: 8px;
      padding: 15px 20px;
      margin: 20px 0;
      font-size: 15px;
    }

    .credentials p {
      margin: 8px 0;
    }

    .credentials span {
      font-weight: bold;
      color: #222;
    }

    .btn {
      display: inline-block;
      background: #53B0B7;
      color: #fff;
      padding: 10px 25px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
    }

    .btn:hover {
      background: #3f959b;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #888;
      padding: 15px;
      border-top: 1px solid #e6e9ec;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(15px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class=\"container\">
    <!-- HEADER -->
    <div class=\"header\">
      <img src=\"https://mydepps.net/_files/logo-depps.png\" alt=\"Logo DEPPS\">
      <h1>👋 Bienvenue sur DEPPS</h1>
    </div>

    <!-- CONTENT -->
    <div class=\"content\">
      <h2>Votre compte vient d’être créé par un administrateur</h2>
      <p>Bonjour <b>{{ user.prenom }} {{ user.nom }}</b>,</p>
      <p>
        Un compte vient d’être créé pour vous sur la plateforme <b>MyDEPPS</b>.  
        Vous pouvez dès maintenant vous connecter en utilisant les informations ci-dessous :
      </p>

      <div class=\"credentials\">
        <p><span>Adresse e-mail :</span> {{ user.email }}</p>
        <p><span>Mot de passe temporaire :</span> {{ password }}</p>
      </div>

      <p style=\"text-align: center; margin-top: 20px;\">
        <a href=\"{{ login_url }}\" class=\"btn\">Se connecter</a>
      </p>

      <p style=\"margin-top: 25px; font-size: 14px; color: #555;\">
        Pour des raisons de sécurité, nous vous recommandons de modifier votre mot de passe après votre première connexion.
      </p>
    </div>

    <!-- FOOTER -->
    <div class=\"footer\">
      Email automatique — © DEPPS {{ \"now\"|date(\"Y\") }}
    </div>
  </div>
</body>
</html>
", "emails/new.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/new.twig");
    }
}
