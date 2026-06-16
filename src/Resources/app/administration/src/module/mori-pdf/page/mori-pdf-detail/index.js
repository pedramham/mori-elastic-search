
import template from './index.html.twig';

const { Component, Mixin } = Shopware;

Component.register('mori-pdf-detail', {
    template,

    inject: ['repositoryFactory', 'elasticsearchService', 'httpClient'],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        pdfId: {
            type: String,
            required: true
        }
    },

    data() {
        return {
            pdf: null,
            isLoading: false,
            isSaveSuccessful: false
        };
    },

    computed: {
        pdfRepository() {
            return this.repositoryFactory.create('pdf_elastic_search');
        },

        toolbarTitle() {
            return this.pdf ? `Edit: ${this.pdf.title}` : 'Edit PDF';
        }
    },

    created() {
        this.getPdfDetail();
    },

    methods: {
        async getPdfDetail() {
            this.isLoading = true;

            try {
                this.pdf = await this.pdfRepository.get(this.pdfId, Shopware.Context.api);
            } catch (error) {
                this.createNotificationError({
                    message: error.message
                });
            } finally {
                this.isLoading = false;
            }
        },

        async onSave() {
            this.isLoading = true;

            try {
                await this.pdfRepository.save(this.pdf);
                await this.elasticsearchService.updatePdf(this.pdf);
                this.isSaveSuccessful = true;
                this.createNotificationSuccess({
                    message: 'PDF updated successfully'
                });

            } catch (error) {
                this.createNotificationError({
                    message: error.message
                });
                this.isSaveSuccessful = false;
            } finally {
                this.isLoading = false;
            }
        },

        onCancel() {
            this.$router.push({ name: 'mori.pdf.list' });
        }
    }
});