<div>
    <script src="https://unpkg.com/monaco-editor@latest/min/vs/loader.js"></script>
    <div class="flex flex-col items-center justify-center text-white w-screen">
        <h1>Edit {{getLastValue($class)}} {{$field}} {{$id}}</h1>
        <div id="container" style="width:100%;height:80vh;border:1px solid grey"></div>
        <button onClick="editorSave()" class="mt-2 {{getSetting('style.main-button-class')}} hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Save
        </button>
    </div>
    <script>
        require.config({ 
            paths: {
                'vs': 'https://unpkg.com/monaco-editor@latest/min/vs'
            }
        });


        @php
            //we define the brackets and autoClosingPairs for Blade syntax highlighting here has the blade understands we are in php and in a ""/''

            $autoClosingPairs = [
                ['open' => '{{', 'close' => '}}'],
                ['open' => '@if', 'close' => '@endif'],
                ['open' => '@foreach', 'close' => '@endforeach'],
                ['open' => '@for', 'close' => '@endfor'],
                ['open' => '@while', 'close' => '@endwhile'],
                ['open' => '@switch', 'close' => '@endswitch'],
                ['open' => '{', 'close' => '}'],
                ['open' => '[', 'close' => ']'],
                ['open' => '(', 'close' => ')'],
                ['open' => "'", 'close' => "'", "notIn" => ["string", "comment"]],
                ['open' => '"', 'close' => '"', "notIn" => ["string"]],
                ['open' => '/**', 'close' => ' */', "notIn" => ["string"]]
            ];
        
        @endphp

        require(['vs/editor/editor.main'], function() {
            window.monaco = monaco;
            monaco.languages.register({ id: "blade" });
            monaco.languages.setLanguageConfiguration('blade', 
                {
                    "comments": {
                        "lineComment": "//",
                        "blockComment": [
                        "{{--",
                        "--}}"
                        ]
                    },
                    "brackets": [
                        [
                        "{",
                        "}"
                        ],
                        [
                        "[",
                        "]"
                        ],
                        [
                        "(",
                        ")"
                        ]
                    ],
                    "autoClosingPairs": @json($autoClosingPairs),
                    "surroundingPairs": [
                        ["{","}"],
                        ["[","]"],
                        ["(",")"],
                        ["'","'"],
                        ["\"","\""]
                    ],
                    "indentationRules": {
                        "increaseIndentPattern": new RegExp("^((?!#).)*(\\{[^}\"']*|\\([^)\"']*|\\[[^\\]\"']*)$"),
                        "decreaseIndentPattern": new RegExp("^((?!.*?\\/\\*).*\\*/)?\\s*[\\)\\}\\]].*$")
                    },
                    "onEnterRules": [
                        {
                            "beforeText": new RegExp("^\\s*(?:def|do|class|for|iter|if|else|while|using|catch).*?:\\s*$"),
                            "action": {
                                "indent": "indent"
                            }
                        },
                        {
                            "beforeText": new RegExp("^\\s*\\}?\\s*(?:return|continue|break)\\s*$"),
                            "action": {
                                "indent": "outdent"
                            }
                        },
                        {
                            "beforeText": new RegExp("^\\s*\\/\\*\\*(?!\\/)([^\\*]|\\*(?!\\/))*$"),
                            "afterText": "^\\s*\\*\\/$",
                            "action": {
                                "indent": "indentOutdent",
                                "appendText": " * "
                            }
                        },
                        {
                            "beforeText": new RegExp("^\\s*\\/\\*\\*(?!\\/)([^\\*]|\\*(?!\\/))*$"),
                            "action": {
                                "indent": "none",
                                "appendText": " * "
                            }
                        },
                        {
                            "beforeText": new RegExp("^(\\t|(\\ ))*\\ \\*(\\ ([^\\*]|\\*(?!\\/))*)?$"),
                            "action": {
                                "indent": "none",
                                "appendText": "* "
                            }
                        },
                        {
                            "beforeText": new RegExp("^(\\t|(\\ ))*\\ \\*\\/\\s*$"),
                            "action": {
                                "indent": "none",
                                "removeText": 1
                            }
                        },
                        {
                            "beforeText": new RegExp("^(\\t|(\\ ))*\\ \\*[^/]*\\*\\/\\s*$"),
                            "action": {
                                "indent": "none",
                                "removeText": 1
                            }
                        }
                    ],
                    "wordPattern": new RegExp("(-?\\d*\\.\\d\\w*)|([^\\`\\~\\!\\@\\#\\%\\^\\&\\*\\(\\)\\-\\=\\+\\[\\{\\]\\}\\\\\\|\\;\\:\\'\\\"\\,\\.\\<\\>\\/\\?\\s]+)")
                }
            );
           
            
            monaco.languages.setMonarchTokensProvider('blade', {
                tokenizer: {
                    root: [
                        [/\{\{/, { token: 'delimiter.curly', next: '@expression' }],
                        [/@\w+/, 'keyword'],
                        [/\w+/, 'identifier'],
                        [/[{}()\[\]]/, '@brackets']
                    ],
                    expression: [
                        [/\}\}/, { token: 'delimiter.curly', next: '@pop' }],
                        [/[^{}]+/, 'string']
                    ]
                }
            });

            const editor = monaco.editor.create(document.getElementById('container'), {
                value: `{!! $content !!}`,
                language:"{{ $language }}",
                theme: 'vs-dark',
                automaticLayout: true,
                
            });
            window.monacoEditor = editor;
        });
       
        function editorSave() {
            const editor = window.monacoEditor;
            if (!editor) {
                console.error('Monaco editor is not initialized');
                return;
            }
            if (editor) {
                const content = editor.getValue();
                Livewire.dispatch('save', { content: content });
            }
        }
    </script>
</div>
