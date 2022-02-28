import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { _get } from "../fetch";
import useGlobalState from "../state";

export default function Posts() {
  const [posts, setPosts] = useState([]);
  const [token, setToken] = useGlobalState("token");

  useEffect(() => {
    const getPosts = async () => {
      const response = await _get("posts", {
        token,
      });

      // Auth failed
      if (response.data?.errorCode === 0) {
        localStorage.removeItem("token");
        setToken(false);
        return;
      }

      setPosts(response.data);
    };

    getPosts();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <div className="posts">
      <h2>Posts</h2>
      <Link to="/create-post">Create post</Link>
      {posts.map((post, index) => (
        <div key={index}>
          <Link to={`/edit-post/${post.id}`}>{post.title ?? "Untitled"}</Link>-
          <Link to={`/delete-post/${post.id}`}>Delete</Link>
        </div>
      ))}
    </div>
  );
}
