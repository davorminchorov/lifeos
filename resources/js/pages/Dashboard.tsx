import React, { useEffect, useState } from "react";
import axios from "axios";

export default function Dashboard() {
  const [user, setUser] = useState<any>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch the authenticated user data
    const fetchUser = async () => {
      try {
        const response = await axios.get("/api/user");
        setUser(response.data);
        setLoading(false);
      } catch (error) {
        console.error("Error fetching user data:", error);
        // Redirect to login if not authenticated
        window.location.href = "/login";
      }
    };

    fetchUser();
  }, []);

  const handleLogout = async () => {
    try {
      await axios.post("/api/logout");
      window.location.href = "/login";
    } catch (error) {
      console.error("Error during logout:", error);
    }
  };

  if (loading) {
    return (
      <div className="container">
        <div className="w-full max-w-4xl">
          <div className="w-full h-12 bg-teal-600 flex items-center pl-4 text-white text-lg font-bold">
            LifeOS
          </div>
          <div className="login-box text-center py-12">
            Loading...
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container">
      <div className="w-full max-w-4xl">
        <div className="w-full h-12 bg-teal-600 flex items-center justify-between px-4 text-white">
          <div className="text-lg font-bold">LifeOS</div>
          <button
            onClick={handleLogout}
            className="px-3 py-1 bg-white text-teal-600 rounded-md text-sm"
          >
            Logout
          </button>
        </div>

        <div className="login-box mt-4">
          <h1 className="text-slate-800 mb-4">Welcome, {user?.name}</h1>

          <div className="mb-6">
            <p className="text-slate-600">
              You are now logged into LifeOS. This is a placeholder dashboard that will be developed further.
            </p>
          </div>

          <div className="border-t border-gray-200 pt-4">
            <h2 className="text-lg font-semibold text-slate-800 mb-2">Getting Started</h2>
            <ul className="list-disc pl-5 text-slate-600">
              <li className="mb-1">Set up your first subscription</li>
              <li className="mb-1">Track your expenses</li>
              <li className="mb-1">Manage your bills</li>
              <li className="mb-1">Create investment goals</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );
}
