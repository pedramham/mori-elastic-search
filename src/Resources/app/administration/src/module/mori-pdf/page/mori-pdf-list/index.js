import template from './index.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('mori-pdf-list', {
    template,

    inject: ['repositoryFactory', 'acl', 'elasticsearchService', 'httpClient'],


    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            pdfs: null,
            isLoading: false,
            showDeleteModal: false,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            deletePdfId: null,
            deletePdfMediaId: null,
        };
    },

    computed: {
        pdfRepository() {
            return this.repositoryFactory.create('pdf_elastic_search');
        },

        columns() {
            return [
                {
                    property: 'title',
                    label: 'Title',
                    allowResize: true,
                    sortable: false,
                    primary: true
                },
                {
                    property: 'description',
                    label: 'Description',
                    sortable: false,
                },
                {
                    property: 'active',
                    label: 'Active',
                    align: 'center',
                    sortable: true,
                },
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    label: 'Created',
                    sortable: true,
                }
            ];
        },
        pdfCriteria() {
            const criteria = new Criteria();

            if (this.term) {
                criteria.setTerm(this.term);
            }

            criteria.addSorting(
                Criteria.sort(this.sortBy, this.sortDirection),
            );
            return criteria;
        }
    },

    created() {
        this.getList();
    },

    methods: {
        async getList() {

            this.isLoading = true;
            return this.pdfRepository.search(this.pdfCriteria, Shopware.Context.api)
                .then((searchResult) => {
                    this.pdfs = searchResult;
                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },
        refreshList() {
            this.getList();
        },

        onDelete(item) {
            console.log("onDelete called", item);
            this.deletePdfId = item.id;
            this.deletePdfMediaId = item.mediaId;
            this.showDeleteModal = true;
        },

        async confirmDelete() {
            try {
                await this.pdfRepository.delete(this.deletePdfId, Shopware.Context.api);

                if (this.elasticsearchService && this.deletePdfMediaId) {
                    await this.elasticsearchService.deleteFromElasticsearch(this.deletePdfMediaId);
                }

                this.createNotificationSuccess({
                    message: 'PDF deleted successfully'
                });

                this.showDeleteModal = false;
                this.getList();
            } catch (error) {

                this.createNotificationError({
                    message: error.message || 'Failed to delete PDF'
                });
            }
        },

        closeDeleteModal() {
            this.showDeleteModal = false;
            this.deletePdfId = null;
            this.deletePdfMediaId = null;
        },
    }
});