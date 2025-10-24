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

/* emails/otp.html.twig */
class __TwigTemplate_18baff3f41ff56eba91525dab2c44c9b extends Template
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
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/otp.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "emails/otp.html.twig"));

        // line 1
        yield "<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"UTF-8\">
  <title>Code OTP</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border: 1px solid #e6e9ec;
    }

    .header {
      background: #f0f9fa;
      padding: 20px;
      text-align: center;
      border-bottom: 2px solid #53B0B7;
    }

    .header h1 {
      margin: 0;
      font-size: 22px;
      color: #222;
    }

    .header small {
      color: #666;
      font-size: 14px;
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
    }

    .otp-box {
      background: #eaf7f8;
      border: 1px solid #53B0B7;
      border-radius: 8px;
      text-align: center;
      padding: 25px 15px;
      margin: 25px 0;
    }

    .otp-box strong {
      display: block;
      font-size: 14px;
      color: #333;
      margin-bottom: 10px;
    }

    .otp-code {
      font-size: 34px;
      font-weight: bold;
      letter-spacing: 6px;
      color: #53B0B7;
    }

    .alert {
      background: #fff7e6;
      border: 1px solid #ffe08a;
      color: #aa7c00;
      padding: 12px 15px;
      border-radius: 6px;
      font-size: 14px;
      margin-top: 20px;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #888;
      padding: 15px;
      border-top: 1px solid #e6e9ec;
    }
  </style>
</head>
<body>
  <div class=\"container\">
    <!-- HEADER -->
    <div class=\"header\">
      <h1>DEPPS</h1>
      <small>Sécurité avant tout</small>
    </div>

    <!-- CONTENT -->
    <div class=\"content\">
      <h2>🔐 Code de vérification</h2>
      <p>Bonjour <b>";
        // line 108
        yield (((($tmp = CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 108, $this->source); })()), "nom", [], "any", false, false, false, 108)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 108, $this->source); })()), "nom", [], "any", false, false, false, 108), "html", null, true)) : ($this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, (isset($context["info_user"]) || array_key_exists("info_user", $context) ? $context["info_user"] : (function () { throw new RuntimeError('Variable "info_user" does not exist.', 108, $this->source); })()), "login", [], "any", false, false, false, 108), "html", null, true)));
        yield "</b>,</p>
      <p>Vous avez demandé un code de vérification pour réinitialiser votre mot de passe.</p>

      <!-- OTP BLOCK -->
      <div class=\"otp-box\">
        <strong>Votre code de vérification :</strong>
        <div class=\"otp-code\">";
        // line 114
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((isset($context["otp_code"]) || array_key_exists("otp_code", $context) ? $context["otp_code"] : (function () { throw new RuntimeError('Variable "otp_code" does not exist.', 114, $this->source); })()), "html", null, true);
        yield "</div>
        <small>⏳ Ce code expire dans <b>15 minutes</b>.</small>
      </div>

      <div class=\"alert\">
        ⚠️ Important : Ne partagez jamais ce code avec qui que ce soit.
      </div>
    </div>

    <!-- FOOTER -->
    <div class=\"footer\">
      © DEPPS — Tous droits réservés
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
        return "emails/otp.html.twig";
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
        return array (  166 => 114,  157 => 108,  48 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("<!DOCTYPE html>
<html lang=\"fr\">
<head>
  <meta charset=\"UTF-8\">
  <title>Code OTP</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #ffffff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      border: 1px solid #e6e9ec;
    }

    .header {
      background: #f0f9fa;
      padding: 20px;
      text-align: center;
      border-bottom: 2px solid #53B0B7;
    }

    .header h1 {
      margin: 0;
      font-size: 22px;
      color: #222;
    }

    .header small {
      color: #666;
      font-size: 14px;
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
    }

    .otp-box {
      background: #eaf7f8;
      border: 1px solid #53B0B7;
      border-radius: 8px;
      text-align: center;
      padding: 25px 15px;
      margin: 25px 0;
    }

    .otp-box strong {
      display: block;
      font-size: 14px;
      color: #333;
      margin-bottom: 10px;
    }

    .otp-code {
      font-size: 34px;
      font-weight: bold;
      letter-spacing: 6px;
      color: #53B0B7;
    }

    .alert {
      background: #fff7e6;
      border: 1px solid #ffe08a;
      color: #aa7c00;
      padding: 12px 15px;
      border-radius: 6px;
      font-size: 14px;
      margin-top: 20px;
    }

    .footer {
      text-align: center;
      font-size: 12px;
      color: #888;
      padding: 15px;
      border-top: 1px solid #e6e9ec;
    }
  </style>
</head>
<body>
  <div class=\"container\">
    <!-- HEADER -->
    <div class=\"header\">
      <h1>DEPPS</h1>
      <small>Sécurité avant tout</small>
    </div>

    <!-- CONTENT -->
    <div class=\"content\">
      <h2>🔐 Code de vérification</h2>
      <p>Bonjour <b>{{ info_user.nom ? info_user.nom : info_user.login }}</b>,</p>
      <p>Vous avez demandé un code de vérification pour réinitialiser votre mot de passe.</p>

      <!-- OTP BLOCK -->
      <div class=\"otp-box\">
        <strong>Votre code de vérification :</strong>
        <div class=\"otp-code\">{{ otp_code }}</div>
        <small>⏳ Ce code expire dans <b>15 minutes</b>.</small>
      </div>

      <div class=\"alert\">
        ⚠️ Important : Ne partagez jamais ce code avec qui que ce soit.
      </div>
    </div>

    <!-- FOOTER -->
    <div class=\"footer\">
      © DEPPS — Tous droits réservés
    </div>
  </div>
</body>
</html>
", "emails/otp.html.twig", "/Volumes/konate/ANVOH/mydeep_admin/backend/templates/emails/otp.html.twig");
    }
}
