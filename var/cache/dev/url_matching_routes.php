<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/reset-password/request' => [[['_route' => 'api_reset_password_request', '_controller' => 'App\\Controller\\ResetPasswordController::request'], null, ['POST' => 0], null, false, false, null]],
        '/_wdt/styles' => [[['_route' => '_wdt_stylesheet', '_controller' => 'web_profiler.controller.profiler::toolbarStylesheetAction'], null, null, null, false, false, null]],
        '/_profiler' => [[['_route' => '_profiler_home', '_controller' => 'web_profiler.controller.profiler::homeAction'], null, null, null, true, false, null]],
        '/_profiler/search' => [[['_route' => '_profiler_search', '_controller' => 'web_profiler.controller.profiler::searchAction'], null, null, null, false, false, null]],
        '/_profiler/search_bar' => [[['_route' => '_profiler_search_bar', '_controller' => 'web_profiler.controller.profiler::searchBarAction'], null, null, null, false, false, null]],
        '/_profiler/phpinfo' => [[['_route' => '_profiler_phpinfo', '_controller' => 'web_profiler.controller.profiler::phpinfoAction'], null, null, null, false, false, null]],
        '/_profiler/xdebug' => [[['_route' => '_profiler_xdebug', '_controller' => 'web_profiler.controller.profiler::xdebugAction'], null, null, null, false, false, null]],
        '/_profiler/open' => [[['_route' => '_profiler_open_file', '_controller' => 'web_profiler.controller.profiler::openAction'], null, null, null, false, false, null]],
        '/api/adminDocument' => [[['_route' => 'app_apis_apiadmindocument_index', '_controller' => 'App\\Controller\\Apis\\ApiAdminDocumentController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/adminDocument/create' => [[['_route' => 'app_apis_apiadmindocument_create', '_controller' => 'App\\Controller\\Apis\\ApiAdminDocumentController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/alerte' => [[['_route' => 'app_apis_apialerte_index', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/alerte/create' => [[['_route' => 'app_apis_apialerte_create', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/article' => [[['_route' => 'app_apis_apiarticle_index', '_controller' => 'App\\Controller\\Apis\\ApiArticleController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/article/create' => [[['_route' => 'app_apis_apiarticle_create', '_controller' => 'App\\Controller\\Apis\\ApiArticleController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/avis' => [[['_route' => 'app_apis_apiavis_index', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/avis/create' => [[['_route' => 'app_apis_apiavis_create', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/civilite' => [[['_route' => 'app_apis_apicivilite_index', '_controller' => 'App\\Controller\\Apis\\ApiCiviliteController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/civilite/create' => [[['_route' => 'app_apis_apicivilite_create', '_controller' => 'App\\Controller\\Apis\\ApiCiviliteController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/code' => [[['_route' => 'app_apis_apicode_index', '_controller' => 'App\\Controller\\Apis\\ApiCodeController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/code/create' => [[['_route' => 'app_apis_apicode_create', '_controller' => 'App\\Controller\\Apis\\ApiCodeController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/codeGenerateur' => [[['_route' => 'app_apis_apicodegenerateur_index', '_controller' => 'App\\Controller\\Apis\\ApiCodeGenerateurController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/codeGenerateur/create' => [[['_route' => 'app_apis_apicodegenerateur_create', '_controller' => 'App\\Controller\\Apis\\ApiCodeGenerateurController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/commentaire' => [[['_route' => 'app_apis_apicommentaire_index', '_controller' => 'App\\Controller\\Apis\\ApiCommentaireController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/commentaire/create' => [[['_route' => 'app_apis_apicommentaire_create', '_controller' => 'App\\Controller\\Apis\\ApiCommentaireController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/commune' => [[['_route' => 'app_apis_apicommune_index', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/destinateur' => [[['_route' => 'app_apis_apidestinateur_index', '_controller' => 'App\\Controller\\Apis\\ApiDestinateurController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/destinateur/create' => [[['_route' => 'app_apis_apidestinateur_create', '_controller' => 'App\\Controller\\Apis\\ApiDestinateurController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/direction' => [[['_route' => 'app_apis_apidirection_index', '_controller' => 'App\\Controller\\Apis\\ApiDirectionController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/direction/create' => [[['_route' => 'app_apis_apidirection_create', '_controller' => 'App\\Controller\\Apis\\ApiDirectionController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/district' => [[['_route' => 'app_apis_apidistrict_index', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/etablissement/create' => [[['_route' => 'app_apis_apietablissement_create', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/etablissement' => [[['_route' => 'app_apis_apietablissement_index', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/forum' => [[['_route' => 'app_apis_apiforum_index', '_controller' => 'App\\Controller\\Apis\\ApiForumController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/forum/actif' => [[['_route' => 'app_apis_apiforum_indexactif', '_controller' => 'App\\Controller\\Apis\\ApiForumController::indexActif'], null, ['GET' => 0], null, false, false, null]],
        '/api/forum/create' => [[['_route' => 'app_apis_apiforum_create', '_controller' => 'App\\Controller\\Apis\\ApiForumController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/genre' => [[['_route' => 'app_apis_apigenre_index', '_controller' => 'App\\Controller\\Apis\\ApiGenreController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/genre/create' => [[['_route' => 'app_apis_apigenre_create', '_controller' => 'App\\Controller\\Apis\\ApiGenreController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/libelleGroupe' => [[['_route' => 'app_apis_apilibellegroupe_index', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/libelleGroupe/create' => [[['_route' => 'app_apis_apilibellegroupe_create', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/lieuDiplome' => [[['_route' => 'app_apis_apilieudiplome_index', '_controller' => 'App\\Controller\\Apis\\ApiLieuDiplomeController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/lieuDiplome/create' => [[['_route' => 'app_apis_apilieudiplome_create', '_controller' => 'App\\Controller\\Apis\\ApiLieuDiplomeController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/message' => [[['_route' => 'app_apis_apimessage_index', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/message/create' => [[['_route' => 'app_apis_apimessage_create', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/niveauIntervention' => [[['_route' => 'app_apis_apiniveauintervention_index', '_controller' => 'App\\Controller\\Apis\\ApiNiveauInterventionController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/niveauIntervention/create' => [[['_route' => 'app_apis_apiniveauintervention_create', '_controller' => 'App\\Controller\\Apis\\ApiNiveauInterventionController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/notification' => [[['_route' => 'app_apis_apinotification_index', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/notification/create' => [[['_route' => 'app_apis_apinotification_create', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/info-paiement' => [[['_route' => 'webhook_paiement', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::webHook'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/info-paiement-oep' => [[['_route' => 'webhook_paiement_oep', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::webHookOep'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/info-paiement-renouvellement' => [[['_route' => 'webhook_paiement_renouvellement', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::webHookRenouvellement'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/initiation/transaction' => [[['_route' => 'app_apis_apipaiement_create', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/paiement' => [[['_route' => 'paiement', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::doPaiement'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/inite/oep' => [[['_route' => 'initie_ope', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::initieOpe'], null, ['POST' => 0], null, false, false, null]],
        '/api/paiement/renouvellement' => [[['_route' => 'renouvellement', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::doRenouvellement'], null, ['POST' => 0], null, false, false, null]],
        '/api/pays' => [[['_route' => 'app_apis_apipays_index', '_controller' => 'App\\Controller\\Apis\\ApiPaysController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/pays/create' => [[['_route' => 'app_apis_apipays_create', '_controller' => 'App\\Controller\\Apis\\ApiPaysController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/profession/api/montants' => [[['_route' => 'api_montants', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::getMontantsOptions'], null, ['GET' => 0], null, false, false, null]],
        '/api/profession' => [[['_route' => 'app_apis_apiprofession_index', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/profession/create' => [[['_route' => 'app_apis_apiprofession_create', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/professionnel' => [[['_route' => 'app_apis_apiprofessionnel_index', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/racineSequence' => [[['_route' => 'app_apis_apiracinesequence_index', '_controller' => 'App\\Controller\\Apis\\ApiRacineSequenceController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/racineSequence/create' => [[['_route' => 'app_apis_apiracinesequence_create', '_controller' => 'App\\Controller\\Apis\\ApiRacineSequenceController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/region' => [[['_route' => 'app_apis_apiregion_index', '_controller' => 'App\\Controller\\Apis\\ApiRegionController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/region/create' => [[['_route' => 'app_apis_apiregion_create', '_controller' => 'App\\Controller\\Apis\\ApiRegionController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/resetpassword/reset/email' => [[['_route' => 'app_apis_apiresetpassword_resetemail', '_controller' => 'App\\Controller\\Apis\\ApiResetPasswordController::resetEmail'], null, ['POST' => 0], null, false, false, null]],
        '/api/resetpassword/reset/email/admin' => [[['_route' => 'app_apis_apiresetpassword_resetemailadmin', '_controller' => 'App\\Controller\\Apis\\ApiResetPasswordController::resetEmailAdmin'], null, ['POST' => 0], null, false, false, null]],
        '/api/resetpassword/change/new/access' => [[['_route' => 'app_apis_apiresetpassword_resetnewaccess', '_controller' => 'App\\Controller\\Apis\\ApiResetPasswordController::resetNewAccess'], null, ['PUT' => 0, 'POST' => 1], null, false, false, null]],
        '/api/situationProfessionnelle' => [[['_route' => 'app_apis_apisituationprofessionnelle_index', '_controller' => 'App\\Controller\\Apis\\ApiSituationProfessionnelleController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/situationProfessionnelle/create' => [[['_route' => 'app_apis_apisituationprofessionnelle_create', '_controller' => 'App\\Controller\\Apis\\ApiSituationProfessionnelleController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/specialite' => [[['_route' => 'app_apis_apispecialite_index', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/specialite/create' => [[['_route' => 'app_apis_apispecialite_create', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/statistique/info-dashboard' => [[['_route' => 'app_apis_apistatistique_index', '_controller' => 'App\\Controller\\Apis\\ApiStatistiqueController::index'], null, ['GET' => 0], null, false, false, null]],
        '/api/statistique/civilite' => [[['_route' => 'app_apis_apistatistique_indexcivilite', '_controller' => 'App\\Controller\\Apis\\ApiStatistiqueController::indexCivilite'], null, ['GET' => 0], null, false, false, null]],
        '/api/statistique/generale' => [[['_route' => 'app_apis_apistatistique_indexgeneral', '_controller' => 'App\\Controller\\Apis\\ApiStatistiqueController::indexGeneral'], null, ['GET' => 0], null, false, false, null]],
        '/api/statistique/ville' => [[['_route' => 'app_apis_apistatistique_indexgeolocalisation', '_controller' => 'App\\Controller\\Apis\\ApiStatistiqueController::indexGeolocalisation'], null, ['GET' => 0], null, false, false, null]],
        '/api/statusPro' => [[['_route' => 'app_apis_apistatuspro_index', '_controller' => 'App\\Controller\\Apis\\ApiStatusProController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/statusPro/create' => [[['_route' => 'app_apis_apistatuspro_create', '_controller' => 'App\\Controller\\Apis\\ApiStatusProController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/typeDiplome' => [[['_route' => 'app_apis_apitypediplome_index', '_controller' => 'App\\Controller\\Apis\\ApiTypeDiplomeController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/typeDiplome/create' => [[['_route' => 'app_apis_apitypediplome_create', '_controller' => 'App\\Controller\\Apis\\ApiTypeDiplomeController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/typeDocument' => [[['_route' => 'app_apis_apitypedocument_index', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/typeDocument/all' => [[['_route' => 'app_apis_apitypedocument_indexbylibelle', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::indexByLibelle'], null, ['GET' => 0], null, false, false, null]],
        '/api/typeDocument/create' => [[['_route' => 'app_apis_apitypedocument_create', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/typePersonne' => [[['_route' => 'app_apis_apitypepersonne_index', '_controller' => 'App\\Controller\\Apis\\ApiTypePersonneController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/typePersonne/create' => [[['_route' => 'app_apis_apitypepersonne_create', '_controller' => 'App\\Controller\\Apis\\ApiTypePersonneController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/typeProfession' => [[['_route' => 'app_apis_apitypeprofession_index', '_controller' => 'App\\Controller\\Apis\\ApiTypeProfessionController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/typeProfession/create' => [[['_route' => 'app_apis_apitypeprofession_create', '_controller' => 'App\\Controller\\Apis\\ApiTypeProfessionController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/user' => [[['_route' => 'app_apis_apiuser_index', '_controller' => 'App\\Controller\\Apis\\ApiUserController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/user/liste/instructeur' => [[['_route' => 'app_apis_apiuser_indexinstructeur', '_controller' => 'App\\Controller\\Apis\\ApiUserController::indexInstructeur'], null, ['GET' => 0], null, false, false, null]],
        '/api/user/get/admin' => [[['_route' => 'app_apis_apiuser_indexadmin', '_controller' => 'App\\Controller\\Apis\\ApiUserController::indexAdmin'], null, ['GET' => 0], null, false, false, null]],
        '/api/user/get/user/externe' => [[['_route' => 'app_apis_apiuser_indexuserexterne', '_controller' => 'App\\Controller\\Apis\\ApiUserController::indexUserExterne'], null, ['GET' => 0], null, false, false, null]],
        '/api/user/admin/create' => [[['_route' => 'app_apis_apiuser_create', '_controller' => 'App\\Controller\\Apis\\ApiUserController::create'], null, ['POST' => 0], null, false, false, null]],
        '/api/user/membre/create' => [[['_route' => 'app_apis_apiuser_createmembre', '_controller' => 'App\\Controller\\Apis\\ApiUserController::createMembre'], null, ['POST' => 0], null, false, false, null]],
        '/api/user/modifier/passeword' => [[['_route' => 'app_apis_apiuser_modificationmotpasse', '_controller' => 'App\\Controller\\Apis\\ApiUserController::ModificationMotPasse'], null, ['POST' => 0], null, false, false, null]],
        '/api/user/membre/mot/passe/oublie' => [[['_route' => 'app_apis_apiuser_motpasseoublie', '_controller' => 'App\\Controller\\Apis\\ApiUserController::motPasseOublie'], null, ['PUT' => 0, 'POST' => 1], null, false, false, null]],
        '/api/user/api/reset-password-request' => [[['_route' => 'app_apis_apiuser_requestresetpassword', '_controller' => 'App\\Controller\\Apis\\ApiUserController::requestResetPassword'], null, ['POST' => 0], null, false, false, null]],
        '/api/user/api/reset-password' => [[['_route' => 'app_apis_apiuser_resetpassword', '_controller' => 'App\\Controller\\Apis\\ApiUserController::resetPassword'], null, ['POST' => 0], null, false, false, null]],
        '/api/ValidationWorkflow' => [[['_route' => 'app_apis_apivalidationworkflow_index', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/ville' => [[['_route' => 'app_apis_apiville_index', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::index'], null, ['GET' => 0], null, true, false, null]],
        '/api/login' => [[['_route' => 'app_auth_login', '_controller' => 'App\\Controller\\AuthController::login'], null, ['POST' => 0], null, false, false, null]],
        '/api/reset-password/reset' => [[['_route' => 'api_reset_password', '_controller' => 'App\\Controller\\ResetPasswordController::resetPassword'], null, ['POST' => 0], null, false, false, null]],
        '/api/reset-password/verify-token-expired' => [[['_route' => 'api_verify_token_expired', '_controller' => 'App\\Controller\\ResetPasswordController::verificationTokenExpiere'], null, ['POST' => 0], null, false, false, null]],
        '/' => [[['_route' => 'app.swagger_ui', '_controller' => 'nelmio_api_doc.controller.swagger_ui'], null, ['GET' => 0], null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/qr\\-code/([^/]++)/([\\w\\W]+)(*:35)'
                .'|/_(?'
                    .'|error/(\\d+)(?:\\.([^/]++))?(*:73)'
                    .'|wdt/([^/]++)(*:92)'
                    .'|profiler/(?'
                        .'|font/([^/\\.]++)\\.woff2(*:133)'
                        .'|([^/]++)(?'
                            .'|/(?'
                                .'|search/results(*:170)'
                                .'|router(*:184)'
                                .'|exception(?'
                                    .'|(*:204)'
                                    .'|\\.css(*:217)'
                                .')'
                            .')'
                            .'|(*:227)'
                        .')'
                    .')'
                .')'
                .'|/api/(?'
                    .'|a(?'
                        .'|dminDocument/(?'
                            .'|get/one/([^/]++)(*:282)'
                            .'|update/([^/]++)(*:305)'
                            .'|delete/(?'
                                .'|([^/]++)(*:331)'
                                .'|all(*:342)'
                            .')'
                        .')'
                        .'|lerte/(?'
                            .'|get/(?'
                                .'|all/([^/]++)(*:380)'
                                .'|one/([^/]++)(*:400)'
                            .')'
                            .'|update/([^/]++)(*:424)'
                            .'|delete/(?'
                                .'|([^/]++)(*:450)'
                                .'|all(*:461)'
                            .')'
                        .')'
                        .'|rticle/(?'
                            .'|get/one/([^/]++)(*:497)'
                            .'|update/([^/]++)(*:520)'
                            .'|delete/(?'
                                .'|([^/]++)(*:546)'
                                .'|all(*:557)'
                            .')'
                        .')'
                        .'|vis/(?'
                            .'|avis/by/forum/([^/]++)(*:596)'
                            .'|get/one/([^/]++)(*:620)'
                            .'|update/([^/]++)(*:643)'
                            .'|delete/(?'
                                .'|([^/]++)(*:669)'
                                .'|all(*:680)'
                            .')'
                        .')'
                    .')'
                    .'|c(?'
                        .'|ivilite/(?'
                            .'|get/one/([^/]++)(*:722)'
                            .'|update/([^/]++)(*:745)'
                            .'|delete/(?'
                                .'|([^/]++)(*:771)'
                                .'|all(*:782)'
                            .')'
                        .')'
                        .'|o(?'
                            .'|de(?'
                                .'|/(?'
                                    .'|get/one/([^/]++)(*:821)'
                                    .'|update/([^/]++)(*:844)'
                                    .'|delete/(?'
                                        .'|([^/]++)(*:870)'
                                        .'|all(*:881)'
                                    .')'
                                .')'
                                .'|Generateur/(?'
                                    .'|get/one/([^/]++)(*:921)'
                                    .'|delete/(?'
                                        .'|([^/]++)(*:947)'
                                        .'|all(*:958)'
                                    .')'
                                .')'
                            .')'
                            .'|mm(?'
                                .'|entaire/(?'
                                    .'|get/one/([^/]++)(*:1001)'
                                    .'|update/([^/]++)(*:1025)'
                                    .'|delete/(?'
                                        .'|([^/]++)(*:1052)'
                                        .'|all(*:1064)'
                                    .')'
                                .')'
                                .'|une/(?'
                                    .'|([^/]++)(*:1090)'
                                    .'|get/one/([^/]++)(*:1115)'
                                    .'|create(*:1130)'
                                    .'|update/([^/]++)(*:1154)'
                                    .'|delete/(?'
                                        .'|([^/]++)(*:1181)'
                                        .'|all(*:1193)'
                                    .')'
                                .')'
                            .')'
                        .')'
                    .')'
                    .'|d(?'
                        .'|estinateur/(?'
                            .'|get/one/([^/]++)(*:1241)'
                            .'|update/([^/]++)(*:1265)'
                            .'|delete/(?'
                                .'|([^/]++)(*:1292)'
                                .'|all(*:1304)'
                            .')'
                        .')'
                        .'|i(?'
                            .'|rection/(?'
                                .'|get/one/([^/]++)(*:1346)'
                                .'|update/([^/]++)(*:1370)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:1397)'
                                    .'|all(*:1409)'
                                .')'
                            .')'
                            .'|strict/(?'
                                .'|([^/]++)(*:1438)'
                                .'|get/one/([^/]++)(*:1463)'
                                .'|create(*:1478)'
                                .'|update/([^/]++)(*:1502)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:1529)'
                                    .'|all(*:1541)'
                                .')'
                            .')'
                        .')'
                    .')'
                    .'|etablissement/(?'
                        .'|update/(?'
                            .'|imputation/([^/]++)(*:1600)'
                            .'|([^/]++)(*:1617)'
                        .')'
                        .'|active/([^/]++)(*:1642)'
                        .'|get/one/([^/]++)(*:1667)'
                        .'|delete/([^/]++)(*:1691)'
                    .')'
                    .'|forum/(?'
                        .'|forum/by/user/([^/]++)(*:1732)'
                        .'|get/one/([^/]++)(*:1757)'
                        .'|update/([^/]++)(*:1781)'
                        .'|delete/(?'
                            .'|([^/]++)(*:1808)'
                            .'|all(*:1820)'
                        .')'
                    .')'
                    .'|genre/(?'
                        .'|get/one/([^/]++)(*:1856)'
                        .'|update/([^/]++)(*:1880)'
                        .'|delete/(?'
                            .'|([^/]++)(*:1907)'
                            .'|all(*:1919)'
                        .')'
                    .')'
                    .'|li(?'
                        .'|belleGroupe/(?'
                            .'|all/(?'
                                .'|([^/]++)(*:1965)'
                                .'|oep/([^/]++)(*:1986)'
                            .')'
                            .'|get/one/([^/]++)(*:2012)'
                            .'|update/([^/]++)(*:2036)'
                            .'|delete/(?'
                                .'|([^/]++)(*:2063)'
                                .'|all(*:2075)'
                            .')'
                        .')'
                        .'|euDiplome/(?'
                            .'|get/one/([^/]++)(*:2115)'
                            .'|update/([^/]++)(*:2139)'
                            .'|delete/(?'
                                .'|([^/]++)(*:2166)'
                                .'|all(*:2178)'
                            .')'
                        .')'
                    .')'
                    .'|message/(?'
                        .'|get/(?'
                            .'|one/([^/]++)(*:2220)'
                            .'|all/([^/]++)/([^/]++)(*:2250)'
                        .')'
                        .'|update/([^/]++)(*:2275)'
                        .'|delete/(?'
                            .'|([^/]++)(*:2302)'
                            .'|all(*:2314)'
                        .')'
                    .')'
                    .'|n(?'
                        .'|iveauIntervention/(?'
                            .'|get/one/([^/]++)(*:2366)'
                            .'|update/([^/]++)(*:2390)'
                            .'|delete/(?'
                                .'|([^/]++)(*:2417)'
                                .'|all(*:2429)'
                            .')'
                        .')'
                        .'|otification/(?'
                            .'|by/([^/]++)(*:2466)'
                            .'|nombre/([^/]++)(*:2490)'
                            .'|get/one/([^/]++)(*:2515)'
                            .'|read/([^/]++)(*:2537)'
                            .'|update/([^/]++)(*:2561)'
                            .'|delete/(?'
                                .'|([^/]++)(*:2588)'
                                .'|all(*:2600)'
                            .')'
                        .')'
                    .')'
                    .'|p(?'
                        .'|a(?'
                            .'|iement/(?'
                                .'|historique/([^/]++)(?'
                                    .'|(*:2652)'
                                    .'|(*:2661)'
                                .')'
                                .'|status/renouvellement/([^/]++)(*:2701)'
                                .'|info/transaction/(?'
                                    .'|([^/]++)(*:2738)'
                                    .'|last/transaction/(?'
                                        .'|([^/]++)(*:2775)'
                                        .'|formatter/([^/]++)(*:2802)'
                                    .')'
                                .')'
                                .'|find/one/transaction/([^/]++)(*:2842)'
                                .'|get/transaction/([^/]++)(*:2875)'
                            .')'
                            .'|ys/(?'
                                .'|get/one/([^/]++)(*:2907)'
                                .'|update/([^/]++)(*:2931)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:2958)'
                                    .'|all(*:2970)'
                                .')'
                            .')'
                        .')'
                        .'|rofession(?'
                            .'|/(?'
                                .'|get/(?'
                                    .'|status/paiement/([^/]++)(*:3029)'
                                    .'|by/code/([^/]++)(*:3054)'
                                    .'|one/([^/]++)(*:3075)'
                                .')'
                                .'|update/([^/]++)(*:3100)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:3127)'
                                    .'|all(*:3139)'
                                .')'
                            .')'
                            .'|nel/(?'
                                .'|update/imputation/([^/]++)(*:3183)'
                                .'|existe/code/([^/]++)(*:3212)'
                                .'|imputation/list/([^/]++)(*:3245)'
                                .'|([^/]++)(*:3262)'
                                .'|active/([^/]++)(*:3286)'
                                .'|get/one/([^/]++)(*:3311)'
                                .'|create(*:3326)'
                                .'|update/([^/]++)(*:3350)'
                                .'|de(?'
                                    .'|lete/(?'
                                        .'|([^/]++)(*:3380)'
                                        .'|all(*:3392)'
                                    .')'
                                    .'|sactive/([^/]++)(*:3418)'
                                .')'
                            .')'
                        .')'
                    .')'
                    .'|r(?'
                        .'|acineSequence/(?'
                            .'|get/one/([^/]++)(*:3468)'
                            .'|update/([^/]++)(*:3492)'
                            .'|delete/(?'
                                .'|([^/]++)(*:3519)'
                                .'|all(*:3531)'
                            .')'
                        .')'
                        .'|egion/(?'
                            .'|get/one/([^/]++)(*:3567)'
                            .'|update/([^/]++)(*:3591)'
                            .'|delete/(?'
                                .'|([^/]++)(*:3618)'
                                .'|all(*:3630)'
                            .')'
                        .')'
                    .')'
                    .'|s(?'
                        .'|ituationProfessionnelle/(?'
                            .'|get/one/([^/]++)(*:3689)'
                            .'|update/([^/]++)(*:3713)'
                            .'|delete/(?'
                                .'|([^/]++)(*:3740)'
                                .'|all(*:3752)'
                            .')'
                        .')'
                        .'|pecialite/(?'
                            .'|get/(?'
                                .'|one/([^/]++)(*:3795)'
                                .'|status/paiement/([^/]++)(*:3828)'
                            .')'
                            .'|update/([^/]++)(*:3853)'
                            .'|delete/(?'
                                .'|([^/]++)(*:3880)'
                                .'|all(*:3892)'
                            .')'
                        .')'
                        .'|tat(?'
                            .'|istique/(?'
                                .'|info\\-dashboard/by/typeuser/([^/]++)/([^/]++)(*:3965)'
                                .'|specialite/([^/]++)(*:3993)'
                            .')'
                            .'|usPro/(?'
                                .'|get/one/([^/]++)(*:4028)'
                                .'|update/([^/]++)(*:4052)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:4079)'
                                    .'|all(*:4091)'
                                .')'
                            .')'
                        .')'
                    .')'
                    .'|type(?'
                        .'|D(?'
                            .'|iplome/(?'
                                .'|get/one/([^/]++)(*:4141)'
                                .'|update/([^/]++)(*:4165)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:4192)'
                                    .'|all(*:4204)'
                                .')'
                            .')'
                            .'|ocument/(?'
                                .'|api/type\\-documents/([^/]++)(*:4254)'
                                .'|get/(?'
                                    .'|one/([^/]++)(*:4282)'
                                    .'|liste/doc/([^/]++)(*:4309)'
                                .')'
                                .'|update/(?'
                                    .'|([^/]++)(*:4337)'
                                    .'|multiple/([^/]++)(*:4363)'
                                .')'
                                .'|delete/(?'
                                    .'|([^/]++)(*:4391)'
                                    .'|all(*:4403)'
                                .')'
                            .')'
                        .')'
                        .'|P(?'
                            .'|ersonne/(?'
                                .'|get/one/([^/]++)(*:4446)'
                                .'|update/([^/]++)(*:4470)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:4497)'
                                    .'|all(*:4509)'
                                .')'
                            .')'
                            .'|rofession/(?'
                                .'|get/one/([^/]++)(*:4549)'
                                .'|update/([^/]++)(*:4573)'
                                .'|delete/(?'
                                    .'|([^/]++)(*:4600)'
                                    .'|all(*:4612)'
                                .')'
                            .')'
                        .')'
                    .')'
                    .'|user/(?'
                        .'|check/email/existe/([^/]++)(*:4660)'
                        .'|get/one/([^/]++)(*:4685)'
                        .'|admin/update/([^/]++)(*:4715)'
                        .'|profil/update/([^/]++)(*:4746)'
                        .'|delete/(?'
                            .'|([^/]++)(*:4773)'
                            .'|user/externe/([^/]++)(*:4803)'
                            .'|all(*:4815)'
                        .')'
                        .'|([^/]++)/toggle\\-active(*:4848)'
                    .')'
                    .'|ValidationWorkflow/(?'
                        .'|([^/]++)(*:4888)'
                        .'|get/one/([^/]++)(*:4913)'
                        .'|create(*:4928)'
                        .'|update/([^/]++)(*:4952)'
                        .'|delete/(?'
                            .'|([^/]++)(*:4979)'
                            .'|all(*:4991)'
                        .')'
                    .')'
                    .'|ville/(?'
                        .'|([^/]++)(*:5019)'
                        .'|get/one/([^/]++)(*:5044)'
                        .'|create(*:5059)'
                        .'|update/([^/]++)(*:5083)'
                        .'|delete/(?'
                            .'|([^/]++)(*:5110)'
                            .'|all(*:5122)'
                        .')'
                    .')'
                .')'
                .'|/fichier/ads/([^/]++)(*:5155)'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => 'qr_code_generate', '_controller' => 'Endroid\\QrCodeBundle\\Controller\\GenerateController'], ['builder', 'data'], null, null, false, true, null]],
        73 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        92 => [[['_route' => '_wdt', '_controller' => 'web_profiler.controller.profiler::toolbarAction'], ['token'], null, null, false, true, null]],
        133 => [[['_route' => '_profiler_font', '_controller' => 'web_profiler.controller.profiler::fontAction'], ['fontName'], null, null, false, false, null]],
        170 => [[['_route' => '_profiler_search_results', '_controller' => 'web_profiler.controller.profiler::searchResultsAction'], ['token'], null, null, false, false, null]],
        184 => [[['_route' => '_profiler_router', '_controller' => 'web_profiler.controller.router::panelAction'], ['token'], null, null, false, false, null]],
        204 => [[['_route' => '_profiler_exception', '_controller' => 'web_profiler.controller.exception_panel::body'], ['token'], null, null, false, false, null]],
        217 => [[['_route' => '_profiler_exception_css', '_controller' => 'web_profiler.controller.exception_panel::stylesheet'], ['token'], null, null, false, false, null]],
        227 => [[['_route' => '_profiler', '_controller' => 'web_profiler.controller.profiler::panelAction'], ['token'], null, null, false, true, null]],
        282 => [[['_route' => 'app_apis_apiadmindocument_getone', '_controller' => 'App\\Controller\\Apis\\ApiAdminDocumentController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        305 => [[['_route' => 'app_apis_apiadmindocument_update', '_controller' => 'App\\Controller\\Apis\\ApiAdminDocumentController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        331 => [[['_route' => 'app_apis_apiadmindocument_delete', '_controller' => 'App\\Controller\\Apis\\ApiAdminDocumentController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        342 => [[['_route' => 'app_apis_apiadmindocument_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiAdminDocumentController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        380 => [[['_route' => 'app_apis_apialerte_indexalertebytype', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::indexAlerteByType'], ['type'], ['GET' => 0], null, false, true, null]],
        400 => [[['_route' => 'app_apis_apialerte_getone', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        424 => [[['_route' => 'app_apis_apialerte_update', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        450 => [[['_route' => 'app_apis_apialerte_delete', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        461 => [[['_route' => 'app_apis_apialerte_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiAlerteController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        497 => [[['_route' => 'app_apis_apiarticle_getone', '_controller' => 'App\\Controller\\Apis\\ApiArticleController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        520 => [[['_route' => 'app_apis_apiarticle_update', '_controller' => 'App\\Controller\\Apis\\ApiArticleController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        546 => [[['_route' => 'app_apis_apiarticle_delete', '_controller' => 'App\\Controller\\Apis\\ApiArticleController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        557 => [[['_route' => 'app_apis_apiarticle_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiArticleController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        596 => [[['_route' => 'app_apis_apiavis_avisforum', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::avisForum'], ['idForum'], ['GET' => 0], null, false, true, null]],
        620 => [[['_route' => 'app_apis_apiavis_getone', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        643 => [[['_route' => 'app_apis_apiavis_update', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        669 => [[['_route' => 'app_apis_apiavis_delete', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        680 => [[['_route' => 'app_apis_apiavis_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiAvisController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        722 => [[['_route' => 'app_apis_apicivilite_getone', '_controller' => 'App\\Controller\\Apis\\ApiCiviliteController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        745 => [[['_route' => 'app_apis_apicivilite_update', '_controller' => 'App\\Controller\\Apis\\ApiCiviliteController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        771 => [[['_route' => 'app_apis_apicivilite_delete', '_controller' => 'App\\Controller\\Apis\\ApiCiviliteController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        782 => [[['_route' => 'app_apis_apicivilite_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiCiviliteController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        821 => [[['_route' => 'app_apis_apicode_getone', '_controller' => 'App\\Controller\\Apis\\ApiCodeController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        844 => [[['_route' => 'app_apis_apicode_update', '_controller' => 'App\\Controller\\Apis\\ApiCodeController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        870 => [[['_route' => 'app_apis_apicode_delete', '_controller' => 'App\\Controller\\Apis\\ApiCodeController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        881 => [[['_route' => 'app_apis_apicode_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiCodeController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        921 => [[['_route' => 'app_apis_apicodegenerateur_getone', '_controller' => 'App\\Controller\\Apis\\ApiCodeGenerateurController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        947 => [[['_route' => 'app_apis_apicodegenerateur_delete', '_controller' => 'App\\Controller\\Apis\\ApiCodeGenerateurController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        958 => [[['_route' => 'app_apis_apicodegenerateur_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiCodeGenerateurController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1001 => [[['_route' => 'app_apis_apicommentaire_getone', '_controller' => 'App\\Controller\\Apis\\ApiCommentaireController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1025 => [[['_route' => 'app_apis_apicommentaire_update', '_controller' => 'App\\Controller\\Apis\\ApiCommentaireController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1052 => [[['_route' => 'app_apis_apicommentaire_delete', '_controller' => 'App\\Controller\\Apis\\ApiCommentaireController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1064 => [[['_route' => 'app_apis_apicommentaire_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiCommentaireController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1090 => [[['_route' => 'app_apis_apicommune_indexbyvile', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::indexByVile'], ['ville'], ['GET' => 0], null, false, true, null]],
        1115 => [[['_route' => 'app_apis_apicommune_getone', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1130 => [[['_route' => 'app_apis_apicommune_create', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::create'], [], ['POST' => 0], null, false, false, null]],
        1154 => [[['_route' => 'app_apis_apicommune_update', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1181 => [[['_route' => 'app_apis_apicommune_delete', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1193 => [[['_route' => 'app_apis_apicommune_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiCommuneController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1241 => [[['_route' => 'app_apis_apidestinateur_getone', '_controller' => 'App\\Controller\\Apis\\ApiDestinateurController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1265 => [[['_route' => 'app_apis_apidestinateur_update', '_controller' => 'App\\Controller\\Apis\\ApiDestinateurController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1292 => [[['_route' => 'app_apis_apidestinateur_delete', '_controller' => 'App\\Controller\\Apis\\ApiDestinateurController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1304 => [[['_route' => 'app_apis_apidestinateur_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiDestinateurController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1346 => [[['_route' => 'app_apis_apidirection_getone', '_controller' => 'App\\Controller\\Apis\\ApiDirectionController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1370 => [[['_route' => 'app_apis_apidirection_update', '_controller' => 'App\\Controller\\Apis\\ApiDirectionController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1397 => [[['_route' => 'app_apis_apidirection_delete', '_controller' => 'App\\Controller\\Apis\\ApiDirectionController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1409 => [[['_route' => 'app_apis_apidirection_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiDirectionController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1438 => [[['_route' => 'app_apis_apidistrict_indexbyregion', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::indexByRegion'], ['region'], ['GET' => 0], null, false, true, null]],
        1463 => [[['_route' => 'app_apis_apidistrict_getone', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1478 => [[['_route' => 'app_apis_apidistrict_create', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::create'], [], ['POST' => 0], null, false, false, null]],
        1502 => [[['_route' => 'app_apis_apidistrict_update', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1529 => [[['_route' => 'app_apis_apidistrict_delete', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1541 => [[['_route' => 'app_apis_apidistrict_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiDistrictController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1600 => [[['_route' => 'app_apis_apietablissement_updateimputation', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::updateImputation'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1617 => [[['_route' => 'app_apis_apietablissement_update', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1642 => [[['_route' => 'app_apis_apietablissement_active', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::active'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1667 => [[['_route' => 'app_apis_apietablissement_getone', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1691 => [[['_route' => 'app_apis_apietablissement_delete', '_controller' => 'App\\Controller\\Apis\\ApiEtablissementController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1732 => [[['_route' => 'app_apis_apiforum_forumbyuser', '_controller' => 'App\\Controller\\Apis\\ApiForumController::forumByUser'], ['userId'], ['GET' => 0], null, false, true, null]],
        1757 => [[['_route' => 'app_apis_apiforum_getone', '_controller' => 'App\\Controller\\Apis\\ApiForumController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1781 => [[['_route' => 'app_apis_apiforum_update', '_controller' => 'App\\Controller\\Apis\\ApiForumController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1808 => [[['_route' => 'app_apis_apiforum_delete', '_controller' => 'App\\Controller\\Apis\\ApiForumController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1820 => [[['_route' => 'app_apis_apiforum_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiForumController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1856 => [[['_route' => 'app_apis_apigenre_getone', '_controller' => 'App\\Controller\\Apis\\ApiGenreController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        1880 => [[['_route' => 'app_apis_apigenre_update', '_controller' => 'App\\Controller\\Apis\\ApiGenreController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        1907 => [[['_route' => 'app_apis_apigenre_delete', '_controller' => 'App\\Controller\\Apis\\ApiGenreController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        1919 => [[['_route' => 'app_apis_apigenre_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiGenreController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        1965 => [[['_route' => 'app_apis_apilibellegroupe_indexbylibelle', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::indexByLibelle'], ['code'], ['GET' => 0], null, false, true, null]],
        1986 => [[['_route' => 'app_apis_apilibellegroupe_indexbylibelleoep', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::indexByLibelleOep'], ['id'], ['GET' => 0], null, false, true, null]],
        2012 => [[['_route' => 'app_apis_apilibellegroupe_getone', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        2036 => [[['_route' => 'app_apis_apilibellegroupe_update', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        2063 => [[['_route' => 'app_apis_apilibellegroupe_delete', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        2075 => [[['_route' => 'app_apis_apilibellegroupe_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiLibelleGroupeController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        2115 => [[['_route' => 'app_apis_apilieudiplome_getone', '_controller' => 'App\\Controller\\Apis\\ApiLieuDiplomeController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        2139 => [[['_route' => 'app_apis_apilieudiplome_update', '_controller' => 'App\\Controller\\Apis\\ApiLieuDiplomeController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        2166 => [[['_route' => 'app_apis_apilieudiplome_delete', '_controller' => 'App\\Controller\\Apis\\ApiLieuDiplomeController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        2178 => [[['_route' => 'app_apis_apilieudiplome_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiLieuDiplomeController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        2220 => [[['_route' => 'app_apis_apimessage_getone', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        2250 => [[['_route' => 'app_apis_apimessage_listeconversationparreceiver', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::listeConversationParReceiver'], ['sender', 'receiver'], ['GET' => 0], null, false, true, null]],
        2275 => [[['_route' => 'app_apis_apimessage_update', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        2302 => [[['_route' => 'app_apis_apimessage_delete', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        2314 => [[['_route' => 'app_apis_apimessage_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiMessageController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        2366 => [[['_route' => 'app_apis_apiniveauintervention_getone', '_controller' => 'App\\Controller\\Apis\\ApiNiveauInterventionController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        2390 => [[['_route' => 'app_apis_apiniveauintervention_update', '_controller' => 'App\\Controller\\Apis\\ApiNiveauInterventionController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        2417 => [[['_route' => 'app_apis_apiniveauintervention_delete', '_controller' => 'App\\Controller\\Apis\\ApiNiveauInterventionController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        2429 => [[['_route' => 'app_apis_apiniveauintervention_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiNiveauInterventionController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        2466 => [[['_route' => 'app_apis_apinotification_indexbyuser', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::indexByUser'], ['userId'], ['GET' => 0], null, false, true, null]],
        2490 => [[['_route' => 'app_apis_apinotification_indexnombrenotificationnonlu', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::indexNombreNotificationNonlu'], ['userId'], ['GET' => 0], null, false, true, null]],
        2515 => [[['_route' => 'app_apis_apinotification_getone', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        2537 => [[['_route' => 'app_apis_apinotification_read', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::Read'], ['id'], ['POST' => 0], null, false, true, null]],
        2561 => [[['_route' => 'app_apis_apinotification_update', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        2588 => [[['_route' => 'app_apis_apinotification_delete', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        2600 => [[['_route' => 'app_apis_apinotification_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiNotificationController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        2652 => [[['_route' => 'app_apis_apipaiement_index', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::index'], ['type'], ['GET' => 0], null, false, true, null]],
        2661 => [[['_route' => 'app_apis_apipaiement_indexbyuser', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::indexByUser'], ['userId'], ['GET' => 0], null, false, true, null]],
        2701 => [[['_route' => 'app_apis_apipaiement_status', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::status'], ['userId'], ['GET' => 0], null, false, true, null]],
        2738 => [[['_route' => 'app_apis_apipaiement_indexinfotransaction', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::indexInfoTransaction'], ['transactionId'], ['GET' => 0], null, false, true, null]],
        2775 => [[['_route' => 'app_apis_apipaiement_indexinfotransactionlasttransaction', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::indexInfoTransactionLastTransaction'], ['userId'], ['GET' => 0], null, false, true, null]],
        2802 => [[['_route' => 'app_apis_apipaiement_indexinfotransactionlasttransactionformatter', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::indexInfoTransactionLastTransactionFormatter'], ['userId'], ['GET' => 0], null, false, true, null]],
        2842 => [[['_route' => 'app_apis_apipaiement_indexfindonetransaction', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::indexFindOneTransaction'], ['id'], ['GET' => 0], null, false, true, null]],
        2875 => [[['_route' => 'app_apis_apipaiement_gettransaction', '_controller' => 'App\\Controller\\Apis\\ApiPaiementController::getTransaction'], ['trxReference'], ['GET' => 0], null, false, true, null]],
        2907 => [[['_route' => 'app_apis_apipays_getone', '_controller' => 'App\\Controller\\Apis\\ApiPaysController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        2931 => [[['_route' => 'app_apis_apipays_update', '_controller' => 'App\\Controller\\Apis\\ApiPaysController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        2958 => [[['_route' => 'app_apis_apipays_delete', '_controller' => 'App\\Controller\\Apis\\ApiPaysController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        2970 => [[['_route' => 'app_apis_apipays_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiPaysController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3029 => [[['_route' => 'app_apis_apiprofession_getpaiementstatus', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::getPaiementStatus'], ['code'], ['GET' => 0], null, false, true, null]],
        3054 => [[['_route' => 'app_apis_apiprofession_getbycodes', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::getByCodes'], ['code'], ['GET' => 0], null, false, true, null]],
        3075 => [[['_route' => 'app_apis_apiprofession_getone', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        3100 => [[['_route' => 'app_apis_apiprofession_update', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3127 => [[['_route' => 'app_apis_apiprofession_delete', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        3139 => [[['_route' => 'app_apis_apiprofession_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiProfessionController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3183 => [[['_route' => 'app_apis_apiprofessionnel_updateimputation', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::updateImputation'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3212 => [[['_route' => 'app_apis_apiprofessionnel_getexistecode', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::getExisteCode'], ['code'], ['GET' => 0], null, false, true, null]],
        3245 => [[['_route' => 'app_professionnel_list_by_imputation', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::indexByImputation'], ['id'], ['GET' => 0], null, false, true, null]],
        3262 => [[['_route' => 'app_apis_apiprofessionnel_indexetat', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::indexEtat'], ['status'], ['GET' => 0], null, false, true, null]],
        3286 => [[['_route' => 'app_apis_apiprofessionnel_active', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::active'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3311 => [[['_route' => 'app_apis_apiprofessionnel_getone', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        3326 => [[['_route' => 'create_professionnel', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::create'], [], ['POST' => 0], null, false, false, null]],
        3350 => [[['_route' => 'app_apis_apiprofessionnel_update', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3380 => [[['_route' => 'app_apis_apiprofessionnel_delete', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        3392 => [[['_route' => 'app_apis_apiprofessionnel_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3418 => [[['_route' => 'app_apis_apiprofessionnel_desactive', '_controller' => 'App\\Controller\\Apis\\ApiProfessionnelController::desactive'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3468 => [[['_route' => 'app_apis_apiracinesequence_getone', '_controller' => 'App\\Controller\\Apis\\ApiRacineSequenceController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        3492 => [[['_route' => 'app_apis_apiracinesequence_update', '_controller' => 'App\\Controller\\Apis\\ApiRacineSequenceController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3519 => [[['_route' => 'app_apis_apiracinesequence_delete', '_controller' => 'App\\Controller\\Apis\\ApiRacineSequenceController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        3531 => [[['_route' => 'app_apis_apiracinesequence_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiRacineSequenceController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3567 => [[['_route' => 'app_apis_apiregion_getone', '_controller' => 'App\\Controller\\Apis\\ApiRegionController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        3591 => [[['_route' => 'app_apis_apiregion_update', '_controller' => 'App\\Controller\\Apis\\ApiRegionController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3618 => [[['_route' => 'app_apis_apiregion_delete', '_controller' => 'App\\Controller\\Apis\\ApiRegionController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        3630 => [[['_route' => 'app_apis_apiregion_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiRegionController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3689 => [[['_route' => 'app_apis_apisituationprofessionnelle_getone', '_controller' => 'App\\Controller\\Apis\\ApiSituationProfessionnelleController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        3713 => [[['_route' => 'app_apis_apisituationprofessionnelle_update', '_controller' => 'App\\Controller\\Apis\\ApiSituationProfessionnelleController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3740 => [[['_route' => 'app_apis_apisituationprofessionnelle_delete', '_controller' => 'App\\Controller\\Apis\\ApiSituationProfessionnelleController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        3752 => [[['_route' => 'app_apis_apisituationprofessionnelle_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiSituationProfessionnelleController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3795 => [[['_route' => 'app_apis_apispecialite_getone', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        3828 => [[['_route' => 'app_apis_apispecialite_getpaiementstatus', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::getPaiementStatus'], ['id'], ['GET' => 0], null, false, true, null]],
        3853 => [[['_route' => 'app_apis_apispecialite_update', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        3880 => [[['_route' => 'app_apis_apispecialite_delete', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        3892 => [[['_route' => 'app_apis_apispecialite_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiSpecialiteController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        3965 => [[['_route' => 'app_apis_apistatistique_indexbytypeuser', '_controller' => 'App\\Controller\\Apis\\ApiStatistiqueController::indexByTypeUser'], ['type', 'idUser'], ['GET' => 0], null, false, true, null]],
        3993 => [[['_route' => 'app_apis_apistatistique_indexspecialite', '_controller' => 'App\\Controller\\Apis\\ApiStatistiqueController::indexSpecialite'], ['genre'], ['GET' => 0], null, false, true, null]],
        4028 => [[['_route' => 'app_apis_apistatuspro_getone', '_controller' => 'App\\Controller\\Apis\\ApiStatusProController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4052 => [[['_route' => 'app_apis_apistatuspro_update', '_controller' => 'App\\Controller\\Apis\\ApiStatusProController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4079 => [[['_route' => 'app_apis_apistatuspro_delete', '_controller' => 'App\\Controller\\Apis\\ApiStatusProController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4091 => [[['_route' => 'app_apis_apistatuspro_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiStatusProController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        4141 => [[['_route' => 'app_apis_apitypediplome_getone', '_controller' => 'App\\Controller\\Apis\\ApiTypeDiplomeController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4165 => [[['_route' => 'app_apis_apitypediplome_update', '_controller' => 'App\\Controller\\Apis\\ApiTypeDiplomeController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4192 => [[['_route' => 'app_apis_apitypediplome_delete', '_controller' => 'App\\Controller\\Apis\\ApiTypeDiplomeController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4204 => [[['_route' => 'app_apis_apitypediplome_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiTypeDiplomeController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        4254 => [[['_route' => 'get_type_documents_by_type_personne', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::getByTypePersonne'], ['typePersonneId'], ['GET' => 0], null, false, true, null]],
        4282 => [[['_route' => 'app_apis_apitypedocument_getone', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4309 => [[['_route' => 'app_apis_apitypedocument_getdocbytypepersonne', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::getDocByTypePersonne'], ['idTypePersonne'], ['GET' => 0], null, false, true, null]],
        4337 => [[['_route' => 'app_apis_apitypedocument_update', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4363 => [[['_route' => 'app_apis_apitypedocument_updatemultiple', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::updateMultiple'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4391 => [[['_route' => 'app_apis_apitypedocument_delete', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4403 => [[['_route' => 'app_apis_apitypedocument_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiTypeDocumentController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        4446 => [[['_route' => 'app_apis_apitypepersonne_getone', '_controller' => 'App\\Controller\\Apis\\ApiTypePersonneController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4470 => [[['_route' => 'app_apis_apitypepersonne_update', '_controller' => 'App\\Controller\\Apis\\ApiTypePersonneController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4497 => [[['_route' => 'app_apis_apitypepersonne_delete', '_controller' => 'App\\Controller\\Apis\\ApiTypePersonneController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4509 => [[['_route' => 'app_apis_apitypepersonne_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiTypePersonneController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        4549 => [[['_route' => 'app_apis_apitypeprofession_getone', '_controller' => 'App\\Controller\\Apis\\ApiTypeProfessionController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4573 => [[['_route' => 'app_apis_apitypeprofession_update', '_controller' => 'App\\Controller\\Apis\\ApiTypeProfessionController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4600 => [[['_route' => 'app_apis_apitypeprofession_delete', '_controller' => 'App\\Controller\\Apis\\ApiTypeProfessionController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4612 => [[['_route' => 'app_apis_apitypeprofession_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiTypeProfessionController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        4660 => [[['_route' => 'app_apis_apiuser_getpaiementstatus', '_controller' => 'App\\Controller\\Apis\\ApiUserController::getPaiementStatus'], ['email'], ['GET' => 0], null, false, true, null]],
        4685 => [[['_route' => 'app_apis_apiuser_getone', '_controller' => 'App\\Controller\\Apis\\ApiUserController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4715 => [[['_route' => 'app_apis_apiuser_update', '_controller' => 'App\\Controller\\Apis\\ApiUserController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4746 => [[['_route' => 'app_apis_apiuser_updatemembre', '_controller' => 'App\\Controller\\Apis\\ApiUserController::updateMembre'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4773 => [[['_route' => 'app_apis_apiuser_delete', '_controller' => 'App\\Controller\\Apis\\ApiUserController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4803 => [[['_route' => 'app_apis_apiuser_deleteuserexterne', '_controller' => 'App\\Controller\\Apis\\ApiUserController::deleteUserExterne'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4815 => [[['_route' => 'app_apis_apiuser_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiUserController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        4848 => [[['_route' => 'app_user_toggleactive', '_controller' => 'App\\Controller\\UserController::toggleActive'], ['id'], ['PATCH' => 0], null, false, false, null]],
        4888 => [[['_route' => 'app_apis_apivalidationworkflow_suivibyuser', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::suiviByUser'], ['idPersoone'], ['GET' => 0], null, false, true, null]],
        4913 => [[['_route' => 'app_apis_apivalidationworkflow_getone', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        4928 => [[['_route' => 'app_apis_apivalidationworkflow_create', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::create'], [], ['POST' => 0], null, false, false, null]],
        4952 => [[['_route' => 'app_apis_apivalidationworkflow_update', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        4979 => [[['_route' => 'app_apis_apivalidationworkflow_delete', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        4991 => [[['_route' => 'app_apis_apivalidationworkflow_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiValidationWorkflowController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        5019 => [[['_route' => 'app_apis_apiville_indexbydistrict', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::indexByDistrict'], ['district'], ['GET' => 0], null, false, true, null]],
        5044 => [[['_route' => 'app_apis_apiville_getone', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::getOne'], ['id'], ['GET' => 0], null, false, true, null]],
        5059 => [[['_route' => 'app_apis_apiville_create', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::create'], [], ['POST' => 0], null, false, false, null]],
        5083 => [[['_route' => 'app_apis_apiville_update', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::update'], ['id'], ['PUT' => 0, 'POST' => 1], null, false, true, null]],
        5110 => [[['_route' => 'app_apis_apiville_delete', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::delete'], ['id'], ['DELETE' => 0], null, false, true, null]],
        5122 => [[['_route' => 'app_apis_apiville_deleteall', '_controller' => 'App\\Controller\\Apis\\ApiVilleController::deleteAll'], [], ['DELETE' => 0], null, false, false, null]],
        5155 => [
            [['_route' => 'fichier_index', '_controller' => 'App\\Controller\\FichierController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
