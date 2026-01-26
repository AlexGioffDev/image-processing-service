import { useForm } from "@inertiajs/react";
import AppLayout from "../../Layouts/AppLayout";

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        email: "",
        password: "",
    });

    const submit = (e) => {
        e.preventDefault();
        post("/register-action");
    };

    return (
        <AppLayout>
            <h1>Login</h1>
            {errors.email && <p style={{ color: "red" }}>{errors.email}</p>}

            <form onSubmit={submit}>
                <input
                    type="text"
                    name="name"
                    id="name"
                    placeholder="name"
                    value={data.name}
                    onChange={(e) => setData("name", e.target.value)}
                />
                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="Email"
                    value={data.email}
                    onChange={(e) => setData("email", e.target.value)}
                />
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Password"
                    value={data.password}
                    onChange={(e) => setData("password", e.target.value)}
                />
                <button type="submit" disabled={processing}>
                    {processing ? "Register in..." : "Register"}
                </button>
            </form>
        </AppLayout>
    );
}
