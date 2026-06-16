
import template from './sw-media-quickinfo.html.twig';
import './sw-media-quickinfo.scss'

Shopware.Component.override('sw-media-quickinfo', {
    template,

    emits: ['notification'],
    inject: ['elasticsearchService', 'httpClient'],

    data() {
        return {
            showPdfModal: false,
            isConverting: false
        };
    },

    computed: {
        isPdfFile() {
            if (!this.item) return false;
            const extension = this.item.fileExtension;
            if (extension) {
                return extension.toLowerCase() === 'pdf';
            }
            return false;
        }
    },

    methods: {

        openModalPdf() {
            this.showPdfModal = true;
        },

        closePdfModal() {
            this.showPdfModal = false;
        },

        async convertPdfToText() {
            this.isConverting = true;

            try {
                const result = await this.elasticsearchService.convertPdfToText(
                    this.item.id,
                    this.item.path
                );

                if (result.success) {
                    this.createNotificationSuccess({
                        title: "Success",
                        message: result.message || "PDF converted and indexed successfully"
                    });
                } else {
                    this.createNotificationError({
                        title: 'Error',
                        message: result.message || "Conversion failed"
                    });
                }
            } catch (error) {
                this.createNotificationError({
                    title: 'Error',
                    message: error.message || "Failed to convert PDF"
                });
            } finally {
                this.isConverting = false;
                this.closePdfModal();
            }
        },

    }
});