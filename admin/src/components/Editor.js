import { useState } from "react";
import { v4 as uuidv4 } from "uuid";
import ParagraphBlock from "./ParagraphBlock";
import InsertionPoint from "./InsertionPoint";

const availableBlocks = [
  {
    identifier: "paragraph",
    name: "Paragraph",
    renderer: ParagraphBlock,
  },
];

export default function Editor({ content, onChange }) {
  const [insertionPoint, setInsertionPoint] = useState(false);

  const addContentItem = (identifier, { position, after }) => {
    const newContentItem = { id: uuidv4(), block: identifier, value: "" };

    if (position === "beginning") {
      onChange([newContentItem, ...content]);
    }

    if (position === "after") {
      onChange([
        ...content.flatMap((contentItem) => {
          if (contentItem.id === after) {
            return [contentItem, newContentItem];
          }

          return contentItem;
        }),
      ]);
    }
  };

  const setContentItemValue = (id, value) => {
    const index = content.findIndex((contentItem) => contentItem.id === id);

    if (index !== -1) {
      content[index] = {
        ...content[index],
        value,
      };

      onChange([...content]);
    }
  };

  const removeContentItem = (id) => {
    onChange([...content.filter((contentItem) => contentItem.id !== id)]);
  };

  return (
    <div className="editor">
      <InsertionPoint
        blocks={availableBlocks}
        open={insertionPoint === -1}
        onOpen={() => setInsertionPoint(-1)}
        onAdd={(identifier) =>
          addContentItem(identifier, { position: "beginning" })
        }
      />
      {content.map((item) => {
        const block = availableBlocks.find(
          (block) => block.identifier === item.block
        );

        if (!block) {
          return null;
        }

        return (
          <div key={item.id} className="block">
            <block.renderer
              defaultValue={item.value}
              onChange={(value) => setContentItemValue(item.id, value)}
            />
            <button onClick={() => removeContentItem(item.id)}>X</button>
            <InsertionPoint
              blocks={availableBlocks}
              open={insertionPoint === item.id}
              onOpen={() => setInsertionPoint(item.id)}
              onAdd={(identifier) =>
                addContentItem(identifier, {
                  position: "after",
                  after: item.id,
                })
              }
            />
          </div>
        );
      })}
    </div>
  );
}
