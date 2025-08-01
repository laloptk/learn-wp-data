import { useEffect, useState } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	Spinner,
	Notice
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
	const { status, limit } = attributes;
	const blockProps = useBlockProps();
	const [notes, setNotes] = useState([]);
	const [loading, setLoading] = useState(true);
	const [error, setError] = useState(null);

	useEffect(() => {
		setLoading(true);
		const query = new URLSearchParams({ status, limit }).toString();
		apiFetch({ path: `/learnwpdata/v1/notes?${query}` })
			.then((data) => {
				setNotes(data);
				setLoading(false);
			})
			.catch((err) => {
				setError('Failed to fetch notes.');
				setLoading(false);
			});
	}, [status, limit]);

	return (
		<>
			<InspectorControls>
				<PanelBody title="List Settings">
					<SelectControl
						label="Status"
						value={status}
						options={[
							{ label: 'Published', value: 'published' },
							{ label: 'Draft', value: 'draft' }
						]}
						onChange={(val) => setAttributes({ status: val })}
					/>
					<TextControl
						label="Limit"
						type="number"
						value={limit}
						min={1}
						onChange={(val) => setAttributes({ limit: parseInt(val, 10) })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				{loading && <Spinner />}
				{error && <Notice status="error">{error}</Notice>}
				{notes.map((note) => (
					<div key={note.id}>
						<h4>{note.title}</h4>
						<p>{note.content}</p>
					</div>
				))}
			</div>
		</>
	);
}
