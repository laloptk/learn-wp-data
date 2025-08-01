import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import './style.css';
import './editor.css';

registerBlockType(metadata.name, {
    ...metadata,
    edit: Edit,
    save: () => null // Dynamic block (rendered on server or left empty if no frontend rendering)
});
