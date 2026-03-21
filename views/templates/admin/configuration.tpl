<div class="row">
    <ul class="nav nav-tabs vertical col-lg-2" aria-orientation="vertical">
        <li class="active"><a data-toggle="tab" href="#tabConfig1"><i class="icon-cogs"></i> Configurações</a></li>
        <li class=""><a href="{$url_services}"><i class="icon-truck"></i> Serviços de Envio</a></li>
        <li class=""><a href="{$url_labels}"><i class="icon-file"></i> Etiquetas de Postagem</a></li>
        <li class=""><a href="{$url_tracking}"><i class="icon-search"></i> Rastreamento de Encomendas</a></li>
        <li class=""><a href="{$url_discounts}"><i class="icon-money"></i> Descontos</a></li>
        <li class=""><a href="{$url_cache}"><i class="icon-download"></i> Cache </a></li>
        <li class=""><a href="{$url_requests}"><i class="icon-cloud"></i> Requisições API</a></li>
        
    </ul>

    <div class='tab-content col-lg-10'>
        <div class='tab-pane active' id="tabConfig1">
            <ul class="nav nav-tabs" role="tablist">
                <li class='active'>
                    <a data-toggle="tab" href="#tabInfo">
                        <i class="icon-cogs"></i> Resolução de Problemas
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabAuth">
                        <i class="icon-cogs"></i> Autenticação
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabConfig">
                        <i class="icon-cogs"></i> Configuração
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabSender">
                        <i class="icon-map-marker"></i> Dados do Remetente
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabMapping">
                        <i class="icon-list"></i> Mapeamentos
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabInstall">
                        <i class="icon-question-circle"></i> Instalação
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabHelp">
                        <i class="icon-question-circle"></i> Ajuda
                    </a>
                </li>

                <li>
                    <a data-toggle="tab" href="#tabMaintenance">
                        <i class="icon-question-cogs"></i> Manutenção
                    </a>
                </li>
            </ul>
            <div class='tab-content'>
                <div class='tab-pane active in' id="tabInfo">
                    <div class='panel'>
                        {if $config_warnings|count == 0 && $config_errors|count == 0}
                            <div class='alert alert-success'>Nenhum problema de configuração detectado</div>
                        {else}
                            {if $config_errors|count}
                                <div class='alert alert-danger'>
                                    <ul>
                                        {foreach from=$config_errors item=error}
                                            <li>{$error}</li>
                                        {/foreach}
                                    </ul>
                                </div>
                            {/if}

                            {if $config_warnings|count}
                                <div class='alert alert-warning'>
                                    <ul>
                                        {foreach from=$config_warnings item=warning}
                                            <li> {$warning}</li>
                                        {/foreach}
                                    </ul>
                                </div>
                            {/if}
                        {/if}
                    </div>
                </div>
                
                <div class='tab-pane' id="tabAuth">{$tabs['auth']}</div>     
                <div class="tab-pane" id="tabConfig">{$tabs['config']}</div>
                <div class="tab-pane" id="tabSender">{$tabs['shop_data']}</div>
                <div class="tab-pane" id="tabMapping">{$tabs['mappings']}</div>
                <div class="tab-pane" id="tabInstall">
                    <div class="panel">
                        <ol>
                            <li>Faça o seu cadastro no site do <a href="https://melhorenvio.com.br/p/ztgd28tOmO" target="_blank">Melhor Envio</a>;</li>
                            <li>Na <a href="https://www.melhorenvio.com.br/painel/gerenciar/tokens" target="_blank">página de tokens</a> clique em "Novo token" e copie o token de acesso que será gerado;</a>
                            <li>Na aba "Configurações" do módulo do Melhor Envio, informe o e-mail de cadastro de sua conta e o token copiado;</li>
                            <li>Na aba "Dados do Remetente", configure o endereço a partir do qual os pacotes serão postados e os dados fiscais de sua empresa;</li>
                            <li>Na aba Frete->Melhor Envio->Serviços, instale as transportadoras que deseja trabalhar.</li>
                        </ol>
                    </div>
                </div>

                <div class='tab-pane' id="tabHelp">
                    <div class='panel'>
                        {include file=$modules_path|cat:"agcliente/views/templates/hook/includes/tab_help.tpl"}
                    </div>
                </div>
                <div class='tab-pane' id="tabMaintenance">
                    <div class='panel'>
                        {$tabs['maintenance']}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>