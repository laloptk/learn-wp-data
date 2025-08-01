import {useState, useEffect} from "@wordpress/element";
import apiFetch from '@wordpress/api-fetch';

const useFetch = (path) => {
    if (!path) return;
    
    const [loading, setLoading] = useState(false);
    const [data, setData] = useState([]);
    const [error, setError] = useState('');

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true);
            try {
                const result = await apiFetch({ path: path });
                setData(result);
            } catch(error) {
                setError(error.message || 'Unknown error');
            } finally {
                setLoading(false);
            }
        }

        fetchData();
    }, [path])
    

    return [data, loading, error];
}

export default useFetch;