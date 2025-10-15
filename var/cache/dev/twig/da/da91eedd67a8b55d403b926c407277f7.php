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

/* emails/vente_email.html.twig */
class __TwigTemplate_ffec6858784eda4eefadf79c7a3dbb9f extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/vente_email.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/vente_email.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>Notification de Vente</title>
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
      margin: 0;
      font-size: 20px;
      color: #53B0B7;
    }

    .content {
      padding: 30px 25px;
      font-size: 15px;
      color: #444;
      line-height: 1.6;
      text-align: center;
    }

    .content h2 {
      font-size: 20px;
      margin-bottom: 15px;
      color: #222;
    }

    .vente {
      background: #eaf7f8;
      border: 1px solid #53B0B7;
      border-radius: 8px;
      text-align: center;
      padding: 18px;
      margin: 20px auto;
      font-size: 20px;
      font-weight: bold;
      color: #53B0B7;
      display: inline-block;
      min-width: 220px;
    }

    .details {
      margin-top: 15px;
      font-size: 15px;
      color: #333;
      line-height: 1.8;
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
      <img src=\"https://via.placeholder.com/140x40?text=ATELIYA\" alt=\"Logo Ateliya\">
      <h1>💰 Notification de Vente</h1>
    </div>

    <!-- CONTENT -->
    <div class=\"content\">
      <h2>Nouvelle Vente ✅</h2>
      <p>Une vente vient d’être effectuée dans la boutique :</p>

      <!-- Nom boutique -->
      <div class=\"vente\">
        ";
        // line 109
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["boutique_libelle"]) || array_key_exists("boutique_libelle", $context) ? $context["boutique_libelle"] : (function () { throw new RuntimeError('Variable "boutique_libelle" does not exist.', 109, $this->source); })()), "html", null, true);
        yield "
      </div>

      <!-- Montant si défini -->
      ";
        // line 113
        if (array_key_exists("montant", $context)) {
            // line 114
            yield "      <div class=\"details\">
        💵 <strong>Montant :</strong> ";
            // line 115
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["montant"]) || array_key_exists("montant", $context) ? $context["montant"] : (function () { throw new RuntimeError('Variable "montant" does not exist.', 115, $this->source); })()), "html", null, true);
            yield " FCFA
      </div>
      ";
        }
        // line 118
        yield "    </div>

    <!-- FOOTER -->
    <div class=\"footer\">
      © ATELIYA — Gestion des ventes
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
        return "emails/vente_email.html.twig";
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
        return array (  176 => 118,  170 => 115,  167 => 114,  165 => 113,  158 => 109,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  <title>Notification de Vente</title>
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
      margin: 0;
      font-size: 20px;
      color: #53B0B7;
    }

    .content {
      padding: 30px 25px;
      font-size: 15px;
      color: #444;
      line-height: 1.6;
      text-align: center;
    }

    .content h2 {
      font-size: 20px;
      margin-bottom: 15px;
      color: #222;
    }

    .vente {
      background: #eaf7f8;
      border: 1px solid #53B0B7;
      border-radius: 8px;
      text-align: center;
      padding: 18px;
      margin: 20px auto;
      font-size: 20px;
      font-weight: bold;
      color: #53B0B7;
      display: inline-block;
      min-width: 220px;
    }

    .details {
      margin-top: 15px;
      font-size: 15px;
      color: #333;
      line-height: 1.8;
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
      <img src=\"https://via.placeholder.com/140x40?text=ATELIYA\" alt=\"Logo Ateliya\">
      <h1>💰 Notification de Vente</h1>
    </div>

    <!-- CONTENT -->
    <div class=\"content\">
      <h2>Nouvelle Vente ✅</h2>
      <p>Une vente vient d’être effectuée dans la boutique :</p>

      <!-- Nom boutique -->
      <div class=\"vente\">
        {{ boutique_libelle }}
      </div>

      <!-- Montant si défini -->
      {% if montant is defined %}
      <div class=\"details\">
        💵 <strong>Montant :</strong> {{ montant }} FCFA
      </div>
      {% endif %}
    </div>

    <!-- FOOTER -->
    <div class=\"footer\">
      © ATELIYA — Gestion des ventes
    </div>
  </div>
</body>
</html>
", "emails/vente_email.html.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/vente_email.html.twig");
    }
}
