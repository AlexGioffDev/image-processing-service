import { Link, router, usePage } from "@inertiajs/react";
import ThemeToogle from "../Components/ThemeToogle";

export default function AppLayout({ children }) {
    const { auth } = usePage().props;

    const handleLogout = (e) => {
        e.preventDefault();
        router.post("/logout");
    };

    return (
        <>
            <header className="w-full max-w-7xl bg-amber-400 text-stone-900 mx-auto p-3">
                <h1>ImageLove</h1>

                <ThemeToogle />
                {auth.user ? (
                    <form onSubmit={handleLogout}>
                        <button type="submit">Logout</button>
                    </form>
                ) : (
                    <>
                        <Link href="/login">Login</Link>
                        <Link href="/register">Register</Link>
                    </>
                )}
            </header>
            <main className="py-2 px-3 max-w-7xl w-full mx-auto">
                {children}
            </main>
        </>
    );
}
