import { useEffect, useRef } from "react";
import "../css/post-title.css";

export default function PostTitle({ defaultValue, onChange }) {
  const ref = useRef(null);

  useEffect(() => {
    if (ref.current) {
      ref.current.style.height = ref.current.scrollHeight + "px";
    }
  }, [ref]);
  return (
    <textarea
      ref={ref}
      className="post-title"
      defaultValue={defaultValue}
      onInput={(e) => {
        onChange(e.target.value);
        ref.current.style.height = "0px";
        ref.current.style.height = ref.current.scrollHeight + "px";
      }}
    />
  );
}
