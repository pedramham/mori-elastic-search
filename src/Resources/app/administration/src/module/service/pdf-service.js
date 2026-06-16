const { Application } = Shopware;

Application.addServiceProvider('elasticsearchService', () => {

    const httpClient = Application.getContainer('init').httpClient;
    const getHeaders = () => ({
        Accept: 'application/vnd.api+json',
        Authorization: `Bearer ${Shopware.Context.api.authToken.access}`,
        'Content-Type': 'application/json'
    });

    const API_DELETE = '/v1/elasticsearch/mori_pdf/delete';
    const API_UPSERT = '/v1/elasticsearch/mori_pdf/upsert'

    return {
        async updatePdf(pdf) {
            try {
                const response = await httpClient.post(
                    API_UPSERT,
                    {
                        mediaId: pdf.mediaId,
                        title: pdf.title,
                        description: pdf.description,
                        url: pdf.path,
                        path: pdf.path,
                        update: true
                    },
                    { headers: getHeaders() }
                );
                return response.data;
            } catch (error) {
                throw error;
            }
        },

        async deleteFromElasticsearch(mediaId) {
            try {
                const response = await httpClient.delete(
                    API_DELETE,
                    {
                        headers: getHeaders(),
                        data: { mediaId: mediaId }
                    }
                );
                return response.data;
            } catch (error) {
                return { success: false, error: error.message };
            }
        },

        async convertPdfToText(mediaId, path) {
            try {
                const response = await httpClient.post(
                    API_UPSERT,
                    {
                        mediaId: mediaId,
                        path: path,
                        update: false
                    },
                    { headers: getHeaders() }
                );
                return response.data;
            } catch (error) {
                throw error;
            }
        }
    };
});