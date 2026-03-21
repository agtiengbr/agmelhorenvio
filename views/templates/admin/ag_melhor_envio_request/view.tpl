<div class='panel'>
    <dl class="dl-horizontal">
        <dt>Endpoint</dt>
        <dd><pre>{$obj->endpoint}</pre></dd>

        <dt>Método</dt>
        <dd><pre>{$obj->method}</pre></dd>

        <dt>Headers</dt>
        <dd><pre>{$obj->headers_display|escape:'html'}</pre></dd>

        <dt>Body</dt>
        <dd><pre>{$obj->body_display|escape:'html'}</pre></dd>

        <dt>Código HTTP</dt>
        <dd><pre>{$obj->http_code}</pre></dd>

        <dt>Resposta</dt>
        <dd><pre>{print_r(($obj->response), true)|escape:'html'}</pre></dd>

        <dt>Tempo</dt>
        <dd><pre>{$obj->time_spent} ms</pre></dd>

        <dt>Data</dt>
        <dd><pre>{Tools::displayDate($obj->date_add)}</pre></dd>
    </dl>
</div>
