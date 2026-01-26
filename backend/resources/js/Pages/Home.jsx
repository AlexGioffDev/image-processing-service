import axios from "../lib/axios";
import AppLayout from "../Layouts/AppLayout";
import { useEffect, useState } from "react";

export default function Home() {
    const [photos, setPhotos] = useState([]);
    const [pagination, setPagination] = useState(null);
    const [loading, setLoading] = useState(false);

    const loadPhotos = async () => {
        setLoading(true);
        try {
            const resp = await axios("/api/photos");
            console.log(resp.data.data);
            setPhotos(resp.data.data);
            setPagination({
                currentPage: resp.data.current_page,
                lastPage: resp.data.last_page,
                total: resp.data.total,
                nextPageUrl: resp.data.next_page_url,
                prevPageUrl: resp.data.prev_page_url,
            });
        } catch (err) {
            console.log(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        loadPhotos();
    }, []);

    return (
        <AppLayout>
            <div className="">
                <h1>Hello React</h1>
                {loading ? (
                    <p>Loading...</p>
                ) : photos.length === 0 ? (
                    <p>You don't have any photos yet</p>
                ) : (
                    <>
                        <img
                            src={`${photos[0]["url"]}`}
                            alt={`${photos[0]["original_name"]}`}
                        />
                        <div className="flex gap-2 items-center ">
                            {photos[0].transformed_versions.map((photo) => (
                                <div
                                    key={photo.id}
                                    className="w-1/3 h-1/3 object-cover"
                                >
                                    <img src={`${photo["url"]}`} />
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
