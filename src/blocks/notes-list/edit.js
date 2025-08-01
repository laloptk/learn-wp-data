import { useEffect, useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	Spinner,
	Notice
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import useFetch from '../../hooks/useFetch';

export default function Edit({ attributes, setAttributes }) {
	const { noteId } = attributes;
	const blockProps = useBlockProps();
	const [note, setNote] = useState(null);
	const [notesList, loading, error] = useFetch('/learnwpdata/v1/notes');

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
