
Shopware.Service('privileges')
    .addPrivilegeMappingEntry({
        category: 'permissions',
        parent: 'content',
        key: 'mori_pdf',
        roles: {
            editor: {
                privileges: [
                    'pdf_elastic_search:update',
                ],
                dependencies: [
                    'mori_pdf.viewer',
                ],
            },
            deleter: {
                privileges: [
                    'pdf_elastic_search:delete',
                ],
                dependencies: [
                    'mori_pdf.viewer',
                ],
            },
        },
    });