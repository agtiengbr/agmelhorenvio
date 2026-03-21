document.addEventListener('DOMContentLoaded', function(){
    addVueComponent();

    function addVueComponent()
    {
        $("<div id='vueApp'><agti-zipcodes-grid v-on:range='range' :rows='ranges'></agti-zipcodes-grid></div>").insertAfter($('[name=active]').closest('.form-group').append());

        var app = new Vue({
            el: '#vueApp',
            data: {
                rangeData: {},
                ranges: [],
                id_discount: ''
            },
            created: function(){
                var form = $('form').serializeArray();
                this.id_discount = form[2].value;
                this.getRanges();
            },
            methods: {
                range: function(range) {
                    this.rangeData = range;
                },
                getRanges: async function() {
                    let data = await axios.get(`${location.href}&getRanges&id_discount=${this.id_discount}`);
                    this.ranges = data.data.ranges;
                    
                    if(this.ranges) {
                        this.ranges.forEach((range) => {
                            range.city = {city: range.city, state: range.state};
                            range.neighborhood = {
                                neighborhood: range.neighborhood, 
                                city: range.city,
                                state: range.state
                            };
                            range.cep_start = 
                                range.cep_start.padStart(8, "0");
                            range.cep_end = 
                                range.cep_end.padStart(8, "0");
                        });
                    }
                }
              }
        });

        $('form').submit(async function(){
            var form = $('form').serializeArray();

            var ranges = JSON.parse(JSON.stringify(app.rangeData));

            let data = new FormData;

            form.forEach(function(k) {
                data.append(k.name, k.value);
            });

            data.append(form[3].name, form[3].value);
            data.delete('bo_search_type');
            data.delete('bo_query');
            data.delete('username_addons');
            data.delete('password_addons');
            
            let url = removeURLParameter(
                removeURLParameter(location.href, 'updateagmelhorenvio_discount'), 
                'id_agmelhorenvio_discount');

            data.append('action', 'save');
            
            //envia o form via ajax
            await axios.post(url, data);

            //if(ranges.length > 0) {

                let dataRanges = new FormData;

                dataRanges.set('id_discount', form[2].value);
            
                ranges.forEach(function(k, i) {
                    dataRanges.set(`ranges[${i}][cep_start]`, k.min);
                    dataRanges.set(`ranges[${i}][cep_end]`, k.max);
                    dataRanges.set(`ranges[${i}][region]`, k.zone);
                    dataRanges.set(`ranges[${i}][city]`, k.city.city);
                    dataRanges.set(`ranges[${i}][neighborhood]`, k.neighborhood.neighborhood);
                    dataRanges.set(`ranges[${i}][state]`, k.state);
                });
    
                //envia as faixas de CEP
                let r = await axios.post(url + '&saveRanges', dataRanges);
                if (!r.data.success) {
                    $.growl.error({title: '', message: r.data.error});
                } else {
                    this.errors = [];
                    $.growl.notice({title: '', message: 'Faixas de CEP salvas com sucesso.'});
                    this.open = false;
                }
            //}

            return true;
        })
    }

    function removeURLParameter(url, parameter) {

        var urlparts = url.split('?');   
        if (urlparts.length >= 2) {
    
            var prefix = encodeURIComponent(parameter) + '=';
            var pars = urlparts[1].split(/[&;]/g);
    
            for (var i = pars.length; i-- > 0;) {    
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                    pars.splice(i, 1);
                }
            }
    
            return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
        }
        return url;
    }
});
