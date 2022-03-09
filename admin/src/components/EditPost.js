import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import useGlobalState from "../state";
import { _get, _post } from "../fetch";
import Editor from "./Editor";
import Page from "./Page";
import PostTitle from "./PostTitle";
import "../css/edit-post.css";

export default function EditPost() {
  const [post, setPost] = useState(false);
  const [token] = useGlobalState("token");
  const { id } = useParams();
  const navigate = useNavigate();

  useEffect(() => {
    const getPost = async () => {
      const response = await _get(`post/${id}`, {
        token,
      });

      // No such post
      if (response.data?.error) {
        navigate("/");
        return;
      }

      setPost(response.data);
    };

    getPost();

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const updatePost = async () => {
    const response = await _post(`post/${id}`, {
      token,
      ...post,
      content: post.content ?? JSON.stringify([]),
    });

    if (response.data?.error) {
      alert(response.data.error);
      return;
    }

    alert("all good");
  };

  if (!post) return null;

  return (
    <Page className="edit-post">
      <PostTitle
        defaultValue={post.title?.trim()}
        onChange={(value) => setPost({ ...post, title: value })}
      />

      <input
        type="text"
        defaultValue={post.slug?.trim()}
        placeholder="Post slug ..."
        onChange={(e) => setPost({ ...post, slug: e.target.value })}
      />

      <input
        type="text"
        defaultValue={post.published_at}
        placeholder="Y-m-d H:i:s"
        onChange={(e) => setPost({ ...post, published_at: e.target.value })}
      />

      <input
        type="text"
        defaultValue={post.status?.trim()}
        placeholder="Status"
        onChange={(e) => setPost({ ...post, status: e.target.value })}
      />

      <Editor
        content={post.content ? JSON.parse(post.content) : []}
        onChange={(content) =>
          setPost({ ...post, content: JSON.stringify(content) })
        }
      />
      <button type="submit" onClick={() => updatePost()}>
        Save
      </button>
    </Page>
  );
}
