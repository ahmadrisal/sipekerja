import express from "express";
import cors from "cors";
import helmet from "helmet";
import dotenv from "dotenv";

dotenv.config();

const app = express();
const PORT = process.env.PORT || 5000;

import authRoutes from "./routes/auth.routes";
import userRoutes from "./routes/user.routes";
import roleRoutes from "./routes/role.routes";
import teamRoutes from "./routes/team.routes";
import ratingRoutes from "./routes/rating.routes";
import exportRoutes from "./routes/export.routes";
import importRoutes from "./routes/import.routes";

app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Active-Role']
}));
app.use(helmet());
app.use(express.json());

app.use("/api/auth", authRoutes);
app.use("/api/users", userRoutes);
app.use("/api/roles", roleRoutes);
app.use("/api/teams", teamRoutes);
app.use("/api/ratings", ratingRoutes);
app.use("/api/exports", exportRoutes);
app.use("/api/import", importRoutes);

app.get("/api/health", (req, res) => {
    res.json({ status: "OK", message: "SIPEKERJA Service is up", timestamp: new Date().toISOString() });
});

app.listen(PORT, () => {
    console.log(`[Backend] Server is running on port ${PORT}`);
});
