package pt.ipleiria.estg.dei.amsi.cinelive;

import android.content.Intent;
import android.os.Bundle;
import android.view.MenuItem;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.navigation.NavController;
import androidx.navigation.fragment.NavHostFragment;
import androidx.navigation.ui.AppBarConfiguration;
import androidx.navigation.ui.NavigationUI;

import com.google.android.material.bottomnavigation.BottomNavigationView;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityMainBinding;

public class MainActivity extends AppCompatActivity {

    private ActivityMainBinding binding;
    private NavController navController;
    private AppBarConfiguration appBarConfiguration;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = ActivityMainBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        WindowCompat.setDecorFitsSystemWindows(getWindow(), false);

        NavHostFragment navHostFragment = (NavHostFragment) getSupportFragmentManager()
                .findFragmentById(R.id.nav_host_fragment);
        navController = navHostFragment.getNavController();

        appBarConfiguration = new AppBarConfiguration.Builder(
                R.id.navFilmes, R.id.navCinemas, R.id.navBilhetes, R.id.navPerfil).build();

        // Toolbar + NavController
        setSupportActionBar(binding.includeToolbar.topAppBar);
        NavigationUI.setupActionBarWithNavController(this, navController, appBarConfiguration);

        // BottomNavigation + NavController
        NavigationUI.setupWithNavController(binding.bottomNav, navController);


        boolean isLoggedIn = true; // TODO: depois substituir por SharedPreferences/token

        // Mostrar 'Entrar' ou 'Perfil'
        binding.bottomNav.getMenu().findItem(R.id.navPerfil)
                .setTitle(isLoggedIn ? R.string.nav_perfil : R.string.nav_entrar);

        // Mostrar 'Bilhetes' se estiver logged in
        binding.bottomNav.getMenu().findItem(R.id.navBilhetes)
                .setVisible(isLoggedIn);


        binding.bottomNav.setOnItemSelectedListener(item -> {

            if (item.getItemId() == R.id.navPerfil) {

                if (!isLoggedIn) {
                    // Redirecionar para LoginActivity
                    startActivity(new Intent(this, LoginActivity.class));
                    return false; // NÃO selecionar o item no bottom nav
                }
            }

            // Comportamento normal do NavigationUI
            NavigationUI.onNavDestinationSelected(item, navController);
            return true;
        });

    }

    @Override
    public boolean onSupportNavigateUp() {
        return navController.navigateUp() || super.onSupportNavigateUp();
    }
}