import { useEffect, useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	Spinner,
	Notice
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
	const { noteId } = attributes;
	const blockProps = useBlockProps();
	const [note, setNote] = useState(null);
	const [notesList, setNotesList] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);

	// Fetch all notes for dropdown
	useEffect(() => {
		apiFetch({ path: '/learnwpdata/v1/notes' })
			.then((notes) => {
				setNotesList(notes);
				setLoading(false);
			})
			.catch((err) => {
				setError('Could not load notes.');
				setLoading(false);
			});
	}, []);

	// Fetch selected note content
	useEffect(() => {
		if (!noteId) return;

		setLoading(true);
		apiFetch({ path: `/learnwpdata/v1/notes/${noteId}` })
			.then((data) => {
				setNote(data);
				setLoading(false);
			})
			.catch((err) => {
				setError('Could not load note.');
				setLoading(false);
			});
	}, [noteId]);

	return (
		<>
			<InspectorControls>
				<PanelBody title="Note Settings">
					<SelectControl
						label="Select Note"
						value={noteId}
						options={[
							{ label: 'Select a note', value: 0 },
							...notesList.map((note) => ({
								label: note.title,
								value: note.id
							}))
						]}
						onChange={(value) => setAttributes({ noteId: parseInt(value, 10) })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{loading && <Spinner />}
				{error && <Notice status="error" isDismissible={false}>{error}</Notice>}
				{note && (
					<>
						<h4>{note.title}</h4>
						<p>{note.content}</p>
					</>
				)}
			</div>
		</>
	);
}
