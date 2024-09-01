(function() {
    tinymce.PluginManager.add('bootstrap_columns', function(editor, url) {
        // Add button for a row with two equal columns
        editor.ui.registry.addButton('two_col', {
            text: '2 Columns',
            icon: 'table', // Use an appropriate icon from TinyMCE or custom
            onAction: function() {
                editor.insertContent('<div class="row"><div class="col-md-6">Column 1</div><div class="col-md-6">Column 2</div></div>');
            }
        });

        // Add button for a row with three equal columns
        editor.ui.registry.addButton('three_col', {
            text: '3 Columns',
            icon: 'table', // Use an appropriate icon from TinyMCE or custom
            onAction: function() {
                editor.insertContent('<div class="row"><div class="col-md-4">Column 1</div><div class="col-md-4">Column 2</div><div class="col-md-4">Column 3</div></div>');
            }
        });

        // Add button for inserting an anchor tag with href and display name
        editor.ui.registry.addButton('insert_anchor', {
            text: 'Anchor',
            icon: 'anchor', // Use an appropriate icon from TinyMCE or custom
            onAction: function() {
                editor.windowManager.open({
                    title: 'Insert Anchor',
                    body: {
                        type: 'panel',
                        items: [
                            {
                                type: 'input',
                                name: 'href',
                                label: 'Href'
                            },
                            {
                                type: 'input',
                                name: 'name',
                                label: 'Name'
                            }
                        ]
                    },
                    buttons: [
                        {
                            type: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            type: 'submit',
                            text: 'Insert',
                            primary: true
                        }
                    ],
                    onSubmit: function(api) {
                        var data = api.getData();
                        editor.insertContent('<a href="' + data.href + '" class="btn btn-size btn btn-custom mob-button1">' + data.name + '</a>');
                        api.close();
                    }
                });
            }
        });

        // Add button for inserting Bootstrap FAQs
        editor.ui.registry.addButton('insert_faq', {
            text: 'Add FAQ',
            icon: 'help', // Use an appropriate icon from TinyMCE or custom
            onAction: function() {
                editor.windowManager.open({
                    title: 'Insert FAQ',
                    body: {
                        type: 'panel',
                        items: [
                            {
                                type: 'input',
                                name: 'question',
                                label: 'Question'
                            },
                            {
                                type: 'textarea',
                                name: 'answer',
                                label: 'Answer'
                            }
                        ]
                    },
                    buttons: [
                        {
                            type: 'cancel',
                            text: 'Cancel'
                        },
                        {
                            type: 'submit',
                            text: 'Insert',
                            primary: true
                        }
                    ],
                    onSubmit: function(api) {
                        var data = api.getData();
                        var question = data.question.trim();
                        var answer = data.answer.trim();

                        var existingFaq = editor.getContent({ format: 'raw' }).includes('accordionExample');
                        var index = existingFaq ? editor.getContent({ format: 'raw' }).match(/id="heading(\d+)"/g).length : 0;

                        var faqHtml = `
                            <div class="card">
                                <div class="card-header" id="heading${index}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse${index}" aria-expanded="${index === 0 ? 'true' : 'false'}" aria-controls="collapse${index}">
                                            ${question}
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapse${index}" class="collapse ${index === 0 ? 'show' : ''}" aria-labelledby="heading${index}" data-parent="#accordionExample">
                                    <div class="card-body">
                                        ${answer}
                                    </div>
                                </div>
                            </div>`;

                        if (existingFaq) {
                            editor.insertContent(faqHtml + '<p>&nbsp;</p>');
                        } else {
                            editor.insertContent('<div class="accordion" id="accordionExample">' + faqHtml + '</div><p>&nbsp;</p>');
                        }
                        api.close();
                    }
                });
            }
        });

        // Add more buttons as needed for different column configurations or functionalities
    });
})();
