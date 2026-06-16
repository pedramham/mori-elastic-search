
import './page/mori-pdf-list';
import './page/mori-pdf-detail';
import './acl';

const { Module } = Shopware;

Module.register('mori-pdf', {
    type: 'plugin',
    name: 'moriElasticSearch.sw-media-quickinfo.name',
    title: 'moriElasticSearch.sw-media-quickinfo.title',
    description: 'moriElasticSearch.sw-media-quickinfo.title',
    color: '#189eff',
    icon: 'regular-file-pdf',

    routes: {
        list: {
            component: 'mori-pdf-list',
            path: 'list',
            meta: {
                parentPath: 'sw.settings.index'
            }
        },
        detail: {
            component: 'mori-pdf-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'mori.pdf.list'
            },
            props: {
                default(route) {
                    return {
                        pdfId: route.params.id
                    };
                }
            }
        }
    },

    navigation: [{
        id: 'mori-pdf',
        label: 'moriElasticSearch.sw-media-quickinfo.name',
        color: '#189eff',
        icon: 'regular-file-pdf',
        path: 'mori.pdf.list',
        parent: 'sw-content',
        position: 70
    }]
});