import java.io.*;
import java.net.*;
import java.util.*;
import java.util.concurrent.*;

public class ServerManager {
    private static Map<Integer, Process> runningServers = new ConcurrentHashMap<>();
    private static Map<Integer, PrintWriter> serverInputs = new ConcurrentHashMap<>();
    
    public static void main(String[] args) {
        if (args.length < 2) {
            System.out.println("Usage: java ServerManager <action> <server_id> [command]");
            return;
        }
        
        String action = args[0];
        int serverId = Integer.parseInt(args[1]);
        
        switch (action) {
            case "start":
                startServer(serverId);
                break;
            case "stop":
                stopServer(serverId);
                break;
            case "restart":
                stopServer(serverId);
                try { Thread.sleep(2000); } catch (Exception e) {}
                startServer(serverId);
                break;
            case "command":
                if (args.length > 2) {
                    executeCommand(serverId, args[2]);
                }
                break;
            case "delete":
                stopServer(serverId);
                deleteServerFiles(serverId);
                break;
        }
    }
    
    private static void startServer(int serverId) {
        try {
            File serverDir = new File("servers/server_" + serverId);
            if (!serverDir.exists()) {
                serverDir.mkdirs();
                downloadServerJar(serverId, serverDir);
                createEula(serverDir);
            }
            
            ProcessBuilder pb = new ProcessBuilder(
                "java", "-Xmx1024M", "-Xms512M", "-jar", "server.jar", "nogui"
            );
            pb.directory(serverDir);
            pb.redirectErrorStream(true);
            
            Process process = pb.start();
            runningServers.put(serverId, process);
            
            PrintWriter writer = new PrintWriter(new OutputStreamWriter(process.getOutputStream()));
            serverInputs.put(serverId, writer);
            
            BufferedReader reader = new BufferedReader(new InputStreamReader(process.getInputStream()));
            new Thread(() -> {
                try {
                    String line;
                    while ((line = reader.readLine()) != null) {
                        logToDatabase(serverId, line);
                    }
                } catch (Exception e) {
                    e.printStackTrace();
                }
            }).start();
            
            System.out.println("Server " + serverId + " started");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    private static void stopServer(int serverId) {
        Process process = runningServers.get(serverId);
        if (process != null && process.isAlive()) {
            executeCommand(serverId, "stop");
            try {
                process.waitFor(10, TimeUnit.SECONDS);
                if (process.isAlive()) {
                    process.destroyForcibly();
                }
            } catch (Exception e) {
                process.destroyForcibly();
            }
            runningServers.remove(serverId);
            serverInputs.remove(serverId);
            System.out.println("Server " + serverId + " stopped");
        }
    }
    
    private static void executeCommand(int serverId, String command) {
        PrintWriter writer = serverInputs.get(serverId);
        if (writer != null) {
            writer.println(command);
            writer.flush();
            System.out.println("Command sent to server " + serverId + ": " + command);
        }
    }
    
    private static void deleteServerFiles(int serverId) {
        File serverDir = new File("servers/server_" + serverId);
        if (serverDir.exists()) {
            deleteDirectory(serverDir);
        }
    }
    
    private static void deleteDirectory(File dir) {
        File[] files = dir.listFiles();
        if (files != null) {
            for (File file : files) {
                if (file.isDirectory()) {
                    deleteDirectory(file);
                } else {
                    file.delete();
                }
            }
        }
        dir.delete();
    }
    
    private static void downloadServerJar(int serverId, File serverDir) throws Exception {
        URL url = new URL("https://api.papermc.io/v2/projects/paper/versions/1.20.1/builds/196/downloads/paper-1.20.1-196.jar");
        HttpURLConnection conn = (HttpURLConnection) url.openConnection();
        
        try (InputStream in = conn.getInputStream();
             FileOutputStream out = new FileOutputStream(new File(serverDir, "server.jar"))) {
            byte[] buffer = new byte[4096];
            int bytesRead;
            while ((bytesRead = in.read(buffer)) != -1) {
                out.write(buffer, 0, bytesRead);
            }
        }
    }
    
    private static void createEula(File serverDir) throws Exception {
        File eulaFile = new File(serverDir, "eula.txt");
        try (PrintWriter writer = new PrintWriter(eulaFile)) {
            writer.println("eula=true");
        }
    }
    
    private static void logToDatabase(int serverId, String logText) {
    }
}
