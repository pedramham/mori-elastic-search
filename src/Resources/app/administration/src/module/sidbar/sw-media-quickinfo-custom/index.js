import template from './sw-media-quickinfo.html.twig';

Shopware.Component.override('sw-media-quickinfo', {
    template,

    emits: ['notification'],

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
            console.log('Convert to text clicked for PDF:', this.item.fileName);
            this.showPdfModal = true;
        },

        closePdfModal() {
            this.showPdfModal = false;
        },

        async convertPdfToText() {
            this.isConverting = true;

            try {

                const requestData = {
                    mediaId: this.item.id,
                    path: this.item.path
                };

                const apiResponse = await fetch('/api/_action/pdf/convert-to-text', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/vnd.api+json',
                        Authorization: `Bearer ${Shopware.Context.api.authToken.access}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const result = await apiResponse.json();

                if (result.success) {
                    this.createNotificationSuccess({
                        title: "success",
                        message: result.message
                    });
                } else {
                    this.createNotificationError({
                        title: 'Error',
                        message: result.message
                    });
                }

            } catch (error) {
                console.log("Error: ".error)
            } finally {
                this.isConverting = false;
                this.closePdfModal();
            }
        },

    }
});