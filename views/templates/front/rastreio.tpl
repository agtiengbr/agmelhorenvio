{extends file='page.tpl'}
{block name='page_content'}
    <h1>{l s='Acompanhar entrega' d='Shop.Theme.Customeraccount'}</h1>

    {if $trackingInfo}
        <p><strong>{l s='Código de rastreio:' d='Shop.Theme.Customeraccount'}</strong> {$trackingInfo}</p>
    {else}
        <p>{l s='Nenhuma informação de rastreio disponível.' d='Shop.Theme.Customeraccount'}</p>
    {/if}

    {if $trackingEvents && count($trackingEvents) > 0}
        <h3>{l s='Eventos' d='Shop.Theme.Customeraccount'}</h3>
        <ul>
            {foreach from=$trackingEvents item=evt}
                <li>{$evt.date_add} - {$evt.tracking_code}</li>
            {/foreach}
        </ul>
    {/if}
{/block}
