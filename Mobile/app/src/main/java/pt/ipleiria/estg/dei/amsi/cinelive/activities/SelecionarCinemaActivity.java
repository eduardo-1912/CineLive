package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.widget.ArrayAdapter;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivitySelecionarCinemaBinding;

public class SelecionarCinemaActivity extends AppCompatActivity {

    ActivitySelecionarCinemaBinding binding;

    // TODO: ELIMINAR ISTO
    private String[] cinemas = {
            "CineLive Leiria",
            "CineLive Lisboa",
            "CineLive Porto",
    };
    private int[] cinemaIDs = {1, 2, 3, 4}; // IDs fake temporários

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        // Obter as preferências do utilizador
        SharedPreferences prefs = getSharedPreferences("UserPreferences", MODE_PRIVATE);

        // Se já tenha cinema --> ir para homepage
        if (prefs.contains("cinema_id")) {
            startActivity(new Intent(this, MainActivity.class));
            finish();
            return;
        }

        // Se não tem cinema --> mostrar activity de seleção
        binding = ActivitySelecionarCinemaBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });


        // Adicionar os cinemas à lista
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
            this, android.R.layout.simple_list_item_1, cinemas
        );
        binding.lvCinemas.setAdapter(adapter);

        // Clique num item da lista
        binding.lvCinemas.setOnItemClickListener((parent, view, position, id) -> {
            int cinemaId = cinemaIDs[position]; // fake por agora

            // Guardar na SharedPreferences
            prefs.edit().putInt("cinema_id", cinemaId).apply();

            // Ir para homepage
            startActivity(new Intent(this, MainActivity.class));
            finish();
        });
    }
}