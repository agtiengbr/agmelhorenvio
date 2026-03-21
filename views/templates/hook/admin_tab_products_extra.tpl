<div class="form-items" data-id-product="{$id_product}">
    <div class="form-group">
        <label class="col-3 control-label">CEP de Origem</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$zipcode}" name="agmelhorenvio_zipcode_origin" placeholder="00000-000"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Nome da Loja</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$shop_name}" name="agmelhorenvio_shop_name"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Logradouro</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$address}" name="agmelhorenvio_address"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Número</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$number}" name="agmelhorenvio_number"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Bairro</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$district}" name="agmelhorenvio_district"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Cidade</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$city}" name="agmelhorenvio_city"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">UF</label>
        <div class="col-6 mb-1">
            <select class="form-control" name="agmelhorenvio_uf">
                <option value='AC' {if $uf == 'AC'}selected{/if}>Acre</option>
                <option value='AL' {if $uf == 'AL'}selected{/if}>Alagoas</option>
                <option value='AP' {if $uf == 'AP'}selected{/if}>Amapá</option>
                <option value='AM' {if $uf == 'AM'}selected{/if}>Amazonas</option>
                <option value='BA' {if $uf == 'BA'}selected{/if}>Bahia</option>
                <option value='CE' {if $uf == 'CE'}selected{/if}>Ceará</option>
                <option value='DF' {if $uf == 'DF'}selected{/if}>Distrito Federal</option>
                <option value='ES' {if $uf == 'ES'}selected{/if}>Espírito Santo</option>
                <option value='GO' {if $uf == 'GO'}selected{/if}>Goiás</option>
                <option value='MA' {if $uf == 'MA'}selected{/if}>Maranhão</option>
                <option value='MT' {if $uf == 'MT'}selected{/if}>Mato Grosso</option>
                <option value='MS' {if $uf == 'MS'}selected{/if}>Mato Grosso do Sul</option>
                <option value='MG' {if $uf == 'MG'}selected{/if}>Minas Gerais</option>
                <option value='PA' {if $uf == 'PA'}selected{/if}>Pará</option>
                <option value='PB' {if $uf == 'PB'}selected{/if}>Paraíba</option>
                <option value='PR' {if $uf == 'PR'}selected{/if}>Paraná</option>
                <option value='PE' {if $uf == 'PE'}selected{/if}>Pernambuco</option>
                <option value='PI' {if $uf == 'PI'}selected{/if}>Piauí</option>
                <option value='RJ' {if $uf == 'RJ'}selected{/if}>Rio de Janeiro</option>
                <option value='RN' {if $uf == 'RN'}selected{/if}>Rio Grande do Norte</option>
                <option value='RS' {if $uf == 'RS'}selected{/if}>Rio Grande do Sul</option>
                <option value='RO' {if $uf == 'RO'}selected{/if}>Rondônia</option>
                <option value='RR' {if $uf == 'RR'}selected{/if}>Roraima</option>
                <option value='SC' {if $uf == 'SC'}selected{/if}>Santa Catarina</option>
                <option value='SP' {if $uf == 'SP'}selected{/if}>São Paulo</option>
                <option value='SE' {if $uf == 'SE'}selected{/if}>Sergipe</option>
                <option value='TO' {if $uf == 'TO'}selected{/if}>Tocantin</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Telefone</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$phone}" name="agmelhorenvio_phone" placeholder="(00) 00000-0000"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">CNPJ</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$cnpj}" name="agmelhorenvio_cnpj" placeholder="00.000.000/0000-00"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">IE</label>
        <div class="col-6 mb-1">
            <input type="text" class="form-control" value="{$ie}" name="agmelhorenvio_ie"/>
        </div>
    </div>

    <div class="form-group">
        <label class="col-3 control-label">Agência JadLog</label>
        <div class="col-6 mb-1">
            <select class="form-control" name="agmelhorenvio_agency_jadlog">
                {foreach from=$agencies_jadlog item=agency}
                    <option value="{$agency['id']}" {if $agency_jadlog == $agency['id']}selected{/if}>{$agency['text']}</option>
                {/foreach}
            </select>
        </div>
    </div>

    {* <div class="form-group">
        <label class="col-3 control-label">Agência Latam</label>
        <div class="col-6 mb-1">
            <select class="form-control" name="agmelhorenvio_agency_latam">
                {foreach from=$agencies_latam item=agency}
                    <option value="{$agency['id']}">{$agency['text']}</option>
                {/foreach}
            </select>
        </div>
    </div> *}

    <button class="ml-3 btn btn-primary">Salvar</button>

</div>