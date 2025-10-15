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

/* emails/welcome_user.html.twig */
class __TwigTemplate_e489f7d86f63c5df93652217eb33d8b0 extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/welcome_user.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/welcome_user.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
  <head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>
      Télécharger notre application
    </title>
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
      text-align: center;
    }

    .header {
      background: #f0f9fa;
      padding: 20px;
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
    }

    .content h2 {
      font-size: 22px;
      margin-bottom: 15px;
      color: #222;
    }

    .qr-code {
      margin: 25px 0;
    }

    .qr-code img {
      border: 2px solid #53B0B7;
      border-radius: 10px;
      padding: 8px;
      background: #f9fdfd;
    }

    .stores {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    .stores img {
      height: 55px;
      transition: transform 0.2s;
    }

    .stores img:hover {
      transform: scale(1.05);
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
        <img src=\"https://via.placeholder.com/140x40?text=DEPPS\" alt=\"Logo DEPPS\">
        <h1>
          🚀 Bienvenue sur ATELIYA
        </h1>
      </div>

      <!-- CONTENT -->
      <div class=\"content\">
        <h2>
          Bonjour
          <b>
            ";
        // line 117
        yield "user.nom";
        yield "
          </b>
          👋
        </h2>
        <p>
          Merci pour votre inscription !
          Pour profiter de toutes nos fonctionnalités, téléchargez dès maintenant notre application mobile :
        </p>

        <!-- QR Code -->
        <div class=\"qr-code\">
          ";
        // line 129
        yield "          <img src=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('Endroid\QrCodeBundle\Twig\QrCodeRuntime')->qrCodeDataUriFunction("Bienvenue utilisateur !"), "html", null, true);
        yield "\" alt=\"QR Code de bienvenue\">
        </div>

        <!-- Boutons Store -->
     <div class=\"stores\">
    <a href=\"https://apps.apple.com/your-app\">
        <img src=\"https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg\" 
             alt=\"Télécharger sur App Store\"
             style=\"height: 45px; width: auto;\">
    </a>
    <a href=\"https://play.google.com/store/apps/details?id=your.app\">
        <img src=\"https://play.google.com/intl/en_us/badges/static/images/badges/fr_badge_web_generic.png\" 
             alt=\"Disponible sur Google Play\"
             style=\"height: 57px; margin-top: -6px;width: auto;\">
    </a>
</div>
      </div>

      <!-- FOOTER -->
      <div class=\"footer\">
        © DEPPS — Votre application de confiance
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
        return "emails/welcome_user.html.twig";
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
        return array (  180 => 129,  166 => 117,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
  <head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>
      Télécharger notre application
    </title>
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
      text-align: center;
    }

    .header {
      background: #f0f9fa;
      padding: 20px;
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
    }

    .content h2 {
      font-size: 22px;
      margin-bottom: 15px;
      color: #222;
    }

    .qr-code {
      margin: 25px 0;
    }

    .qr-code img {
      border: 2px solid #53B0B7;
      border-radius: 10px;
      padding: 8px;
      background: #f9fdfd;
    }

    .stores {
      margin-top: 20px;
      display: flex;
      justify-content: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    .stores img {
      height: 55px;
      transition: transform 0.2s;
    }

    .stores img:hover {
      transform: scale(1.05);
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
        <img src=\"https://via.placeholder.com/140x40?text=DEPPS\" alt=\"Logo DEPPS\">
        <h1>
          🚀 Bienvenue sur ATELIYA
        </h1>
      </div>

      <!-- CONTENT -->
      <div class=\"content\">
        <h2>
          Bonjour
          <b>
            {{ \"user.nom\" }}
          </b>
          👋
        </h2>
        <p>
          Merci pour votre inscription !
          Pour profiter de toutes nos fonctionnalités, téléchargez dès maintenant notre application mobile :
        </p>

        <!-- QR Code -->
        <div class=\"qr-code\">
          {#  <img src=\"{{ qr_code_url }}\" alt=\"QR Code\" width=\"150\"> #}
          <img src=\"{{ qr_code_data_uri('Bienvenue utilisateur !') }}\" alt=\"QR Code de bienvenue\">
        </div>

        <!-- Boutons Store -->
     <div class=\"stores\">
    <a href=\"https://apps.apple.com/your-app\">
        <img src=\"https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg\" 
             alt=\"Télécharger sur App Store\"
             style=\"height: 45px; width: auto;\">
    </a>
    <a href=\"https://play.google.com/store/apps/details?id=your.app\">
        <img src=\"https://play.google.com/intl/en_us/badges/static/images/badges/fr_badge_web_generic.png\" 
             alt=\"Disponible sur Google Play\"
             style=\"height: 57px; margin-top: -6px;width: auto;\">
    </a>
</div>
      </div>

      <!-- FOOTER -->
      <div class=\"footer\">
        © DEPPS — Votre application de confiance
      </div>
    </div>
  </body>
</html>
", "emails/welcome_user.html.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/welcome_user.html.twig");
    }
}
