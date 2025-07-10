<?php
require_once __DIR__ . "/config/db_connection.php"; // Mantém para consistência, mesmo que não use BD
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Política de Privacidade e Termos de Uso do Bauru Serviços. Saiba como protegemos seus dados e quais são seus direitos.">
    <meta name="author" content="Lucas Borges">
    <meta name="keywords" content="política de privacidade, termos de uso, bauru serviços, LGPD, proteção de dados">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade e Termos de Uso - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

    <?php require_once __DIR__ . "/partials/header.php"; ?>
    
    <div class="container">
        <main class="legal-page">
            <div class="legal-container">
                <section class="legal-section">
                    <h2>Política de Privacidade e Termos de Uso</h2>
                    <p class="last-updated">Última atualização: 15 de junho de 2025</p>
                    
                    <div class="legal-content">
                        <h3>1. Informações Gerais</h3>
                        <p>O Bauru Serviços é uma plataforma digital que conecta prestadores de serviços a clientes na cidade de Bauru e região. Esta Política de Privacidade e Termos de Uso estabelece as diretrizes para o uso de nossos serviços e o tratamento de dados pessoais, em conformidade com a Lei Geral de Proteção de Dados (LGPD - Lei nº 13.709/2018).</p>

                        <h3>2. Dados Coletados</h3>
                        <p>Coletamos as seguintes informações dos prestadores de serviços:</p>
                        <ul>
                            <li><strong>Dados de identificação:</strong> Nome completo, data de nascimento</li>
                            <li><strong>Dados de contato:</strong> Telefone, e-mail, endereço</li>
                            <li><strong>Dados profissionais:</strong> Profissão, tempo de experiência, descrição dos serviços</li>
                            <li><strong>Dados de acesso:</strong> Senha criptografada para acesso à plataforma</li>
                            <li><strong>Dados opcionais:</strong> Redes sociais, informações sobre prestação de serviços no endereço</li>
                        </ul>

                        <h3>3. Finalidade do Tratamento</h3>
                        <p>Os dados pessoais são tratados para as seguintes finalidades:</p>
                        <ul>
                            <li>Cadastro e manutenção de perfil na plataforma</li>
                            <li>Exibição de informações para potenciais clientes</li>
                            <li>Comunicação entre a plataforma e o prestador de serviços</li>
                            <li>Melhoria dos serviços oferecidos</li>
                            <li>Cumprimento de obrigações legais</li>
                        </ul>

                        <h3>4. Base Legal</h3>
                        <p>O tratamento de dados pessoais é realizado com base no consentimento do titular, conforme previsto no art. 7º, I da LGPD, e para o cumprimento de obrigação legal ou regulatória pelo controlador.</p>

                        <h3>5. Compartilhamento de Dados</h3>
                        <p>Os dados dos prestadores de serviços são exibidos publicamente na plataforma para facilitar o contato com potenciais clientes. Não compartilhamos dados pessoais com terceiros para fins comerciais sem o consentimento expresso do titular.</p>

                        <h3>6. Segurança dos Dados</h3>
                        <p>Implementamos medidas técnicas e organizacionais adequadas para proteger os dados pessoais contra:</p>
                        <ul>
                            <li>Acesso não autorizado</li>
                            <li>Alteração, destruição ou perda acidental</li>
                            <li>Tratamento ilícito ou inadequado</li>
                        </ul>
                        <p>As senhas são armazenadas utilizando algoritmos de criptografia seguros.</p>

                        <h3>7. Retenção de Dados</h3>
                        <p>Os dados pessoais são mantidos pelo tempo necessário para as finalidades informadas, observando:</p>
                        <ul>
                            <li>Dados de prestadores ativos: mantidos enquanto o cadastro estiver ativo</li>
                            <li>Dados de prestadores inativos: mantidos por 30 dias após a desativação, sendo excluídos automaticamente após este período</li>
                            <li>Dados para cumprimento legal: mantidos pelo prazo exigido pela legislação</li>
                        </ul>

                        <h3>8. Direitos do Titular</h3>
                        <p>Conforme a LGPD, você tem os seguintes direitos:</p>
                        <ul>
                            <li><strong>Confirmação e acesso:</strong> Saber se tratamos seus dados e acessá-los</li>
                            <li><strong>Correção:</strong> Corrigir dados incompletos, inexatos ou desatualizados</li>
                            <li><strong>Anonimização ou eliminação:</strong> Solicitar a anonimização ou eliminação de dados desnecessários</li>
                            <li><strong>Portabilidade:</strong> Solicitar a portabilidade dos dados a outro fornecedor</li>
                            <li><strong>Revogação do consentimento:</strong> Revogar o consentimento a qualquer momento</li>
                        </ul>

                        <h3>9. Termos de Uso</h3>
                        <h4>9.1 Aceitação dos Termos</h4>
                        <p>Ao se cadastrar na plataforma, você concorda com estes Termos de Uso e com nossa Política de Privacidade.</p>

                        <h4>9.2 Responsabilidades do Prestador</h4>
                        <ul>
                            <li>Fornecer informações verdadeiras e atualizadas</li>
                            <li>Manter a confidencialidade de suas credenciais de acesso</li>
                            <li>Prestar serviços de qualidade e com profissionalismo</li>
                            <li>Cumprir as leis e regulamentações aplicáveis</li>
                        </ul>

                        <h4>9.3 Responsabilidades da Plataforma</h4>
                        <ul>
                            <li>Manter a plataforma funcionando adequadamente</li>
                            <li>Proteger os dados pessoais conforme esta política</li>
                            <li>Facilitar o contato entre prestadores e clientes</li>
                        </ul>

                        <h4>9.4 Limitações</h4>
                        <p>A plataforma atua apenas como intermediária, não sendo responsável por:</p>
                        <ul>
                            <li>Qualidade dos serviços prestados</li>
                            <li>Negociações entre prestadores e clientes</li>
                            <li>Danos decorrentes da prestação de serviços</li>
                        </ul>

                        <h3>10. Cookies e Tecnologias Similares</h3>
                        <p>Utilizamos cookies essenciais para o funcionamento da plataforma, incluindo:</p>
                        <ul>
                            <li>Cookies de sessão para manter o login</li>
                            <li>Cookies de segurança</li>
                        </ul>

                        <h3>11. Alterações na Política</h3>
                        <p>Esta Política de Privacidade pode ser atualizada periodicamente. Alterações significativas serão comunicadas através da plataforma.</p>

                        <h3>12. Contato</h3>
                        <p>Para exercer seus direitos ou esclarecer dúvidas sobre esta política, entre em contato conosco:</p>
                        <ul>
                            <li><strong>E-mail:</strong> <a href="mailto:contato@servicosbauru.com.br">contato@servicosbauru.com.br</a></li>
                            <li><strong>Telefone:</strong> Em breve</li>
                        </ul>

                        <h3>13. Foro</h3>
                        <p>Fica eleito o foro da Comarca de Bauru, Estado de São Paulo, para dirimir quaisquer controvérsias decorrentes desta Política de Privacidade e Termos de Uso.</p>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?php require_once __DIR__ . "/partials/footer.php"; ?>

</body>
</html>

<?php
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>