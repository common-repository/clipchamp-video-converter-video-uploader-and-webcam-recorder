/* global clipchamp */
var el = wp.element.createElement,
    registerBlockType = wp.blocks.registerBlockType,
    Editable = wp.blocks.Editable,
    BlockControls = wp.blocks.BlockControls,
    AlignmentToolbar = wp.blocks.BlockAlignmentToolbar,
    InspectorControls = wp.blocks.InspectorControls,
    __ = wp.i18n.__;

    registerBlockType( 'clipchamp/video-uploader', {
    title: __( 'Clipchamp Uploader', 'clipchamp'),

    icon: 'video-alt',

    category: 'widgets',

    keywords: ['clipchamp','video','record'],

    attributes: {
        content: {
            type: 'array',
            source: 'children',
            selector: 'span'
        },
        alignment: {
            type: 'string',
            default: 'none'
        },
        size: {
            type: 'string',
            default: 'medium'
        },
        label: {
            type: 'string',
            default: clipchamp.defaultLabel
        }
    },

    edit: function( props ) {
        var content   = props.attributes.content,
            alignment = props.attributes.alignment,
            size      = props.attributes.size,
            label     = props.attributes.label,
            focus     = props.focus;

        function onChangeContent( newContent ) {
            props.setAttributes( { label: newContent, content: newContent } );
        }

        function onChangeAlignment( newAlignment ) {
            props.setAttributes( { alignment: newAlignment } );
        }

        function onChangeSize( newSize ) {
            props.setAttributes( { size: newSize } );
        }

        function defaultContent() {
            if ( undefined === content || 0 === content.length ) {
                return clipchamp.defaultLabel;
            }
            return content;
        }

        return [
            focus && el(
                BlockControls,
                {
                    key: 'controls'
                },
                el(
                    AlignmentToolbar,
                    {
                        value: alignment,
                        onChange: onChangeAlignment
                    }
                )
            ),
            el(
                'p',
                {
                    className: [props.className, size].join( ' ' ),
                    style: {textAlign: alignment}
                },
                el(
                    Editable,
                    {
                        tagName: 'span',
                        onChange: onChangeContent,
                        value: defaultContent(),
                        focus: focus,
                        onFocus: props.setFocus,
                        formattingControls: [],
                        multiline: false
                    }
                ),
                focus && el(
                    InspectorControls,
                    {
                        key: 'controls'
                    },
                    el(
                        InspectorControls.SelectControl,
                        {
                            label: __( 'Size', 'clipchamp' ),
                            value: size,
                            options: [
                                { value: 'tiny', label: __( 'Tiny', 'clipchamp' ) },
                                { value: 'small', label: __( 'Small', 'clipchamp' ) },
                                { value: 'medium', label: __( 'Medium', 'clipchamp' ) },
                                { value: 'large', label: __( 'Large', 'clipchamp' ) }
                            ],
                            onChange: onChangeSize
                        }
                    )
                )
            ),
            focus && el(
                'p',
                {
                    className: 'clipchamp-context-message'
                },
                __( 'Clipchamp Video Uploader', 'clipchamp' )
            )
        ];
    },

    save: function( props ) {
        var content   = props.attributes.content,
            alignment = props.attributes.alignment,
            size      = props.attributes.size;

        return el( 'p',
            {
                className: [props.className, size].join( ' ' ),
                style: {textAlign: alignment}
            },
            el( 'span', {}, content )
        );
    }
} );
