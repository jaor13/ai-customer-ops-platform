"""
BM25 Sparse Encoder Service
Accepts text, returns sparse vector (indices + values) for Qdrant hybrid search.
"""

from contextlib import asynccontextmanager
from fastapi import FastAPI
from pydantic import BaseModel
from fastembed import SparseTextEmbedding


model = None


@asynccontextmanager
async def lifespan(app: FastAPI):
    global model
    # Load BM25 model on startup (small, fast, no GPU needed)
    model = SparseTextEmbedding(model_name="Qdrant/bm25")
    yield


app = FastAPI(title="Sparse Encoder", lifespan=lifespan)


class EncodeRequest(BaseModel):
    text: str | list[str]


class SparseVector(BaseModel):
    indices: list[int]
    values: list[float]


class EncodeResponse(BaseModel):
    sparse_vectors: list[SparseVector]


@app.post("/encode", response_model=EncodeResponse)
def encode(req: EncodeRequest):
    texts = req.text if isinstance(req.text, list) else [req.text]
    embeddings = list(model.embed(texts))
    sparse_vectors = [
        SparseVector(
            indices=emb.indices.tolist(),
            values=emb.values.tolist(),
        )
        for emb in embeddings
    ]
    return EncodeResponse(sparse_vectors=sparse_vectors)


@app.get("/health")
def health():
    return {"status": "ok", "model": "Qdrant/bm25"}
