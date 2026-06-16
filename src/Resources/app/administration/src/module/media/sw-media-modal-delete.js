
Shopware.Component.override('sw-media-modal-delete', {

    inject: ['elasticsearchService', 'httpClient'],

    methods: {
        async _deleteSelection(item) {

            await this.$super('_deleteSelection', item);
            if (item.getEntityName() === 'media' && item.fileExtension === 'pdf') {
                let deletePdfMediaId = item.id;

                if (this.elasticsearchService && deletePdfMediaId) {
                    await this.elasticsearchService.deleteFromElasticsearch(deletePdfMediaId);
                }
            }

        },
    }
});