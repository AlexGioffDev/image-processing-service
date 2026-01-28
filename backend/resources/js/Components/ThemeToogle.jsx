import { useEffect, useState } from "react";

export default function ThemeToogle()
{
    const [theme, setTheme] = useState(() =>
    {
        if(typeof window !== 'undefined'){
            const saved = localStorage.getItem('theme');
            if (saved) return saved;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return 'light';
    })

    useEffect(() => {
        const root = document.documentElement;

        if(theme == "dark")
        {
            root.classList.add('dark');
        } else {
            root.classList.remove('dark');
        }
        localStorage.setItem('theme', theme);
    }, [theme])

    const toogleTheme = () => { setTheme(pre => pre === 'light' ? 'dark' : 'light')}

    return (
        <div>
            <button onClick={toogleTheme}>
                Toggle
            </button>
        </div>
    )
}
